<?php

namespace Sholokhov\Frontboot\Generator\Extension\Strategy\Vite;

use RuntimeException;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

/**
 * Модифицирует конфигурацию vite
 *
 * Предоставляет удобный fluent interface для добавления импортов,
 * обёртывания defineConfig и добавления различных конфиг-секций.
 */
class ViteConfigModifier
{
    private File $config;
    private readonly bool $isTypescript;

    public function __construct(Directory $directory)
    {
        $this->config = $this->findConfigFile($directory);
        $this->isTypescript = $this->config->getName() === 'vite.config.ts';
    }

    /**
     * Добавить импорт в конфиг
     *
     * @param string $what Что импортировать: 'loadEnv' или 'loadEnv, config'
     * @param string $from Откуда импортировать: 'vite' или '@vitejs/plugin-vue'
     * @return self
     */
    public function addImport(string $what, string $from): self
    {
        $content = $this->getContent();
        $items = $this->parseImportItems($what);

        foreach ($items as $item) {
            if ($this->importExists($item, $from, $content)) {
                continue;
            }

            $content = $this->addImportToContent($item, $from, $content);
        }

        $this->saveContent($content);
        return $this;
    }

    /**
     * Обновить существующий импорт (добавить новые элементы)
     *
     * @param string $additionalExports Что добавить: 'loadEnv' или 'loadEnv, config'
     * @param string $from Из какого модуля
     * @return self
     */
    public function updateImport(string $additionalExports, string $from): self
    {
        $pattern = '/import\s*{\s*([^}]+)\s*}\s*from\s*[\'"]' . preg_quote($from, '/') . '[\'"];?/';

        $content = preg_replace_callback(
            $pattern,
            function ($matches) use ($additionalExports) {
                return $this->mergeImportExports($matches[1], $additionalExports);
            },
            $this->getContent()
        );

        $this->saveContent($content);
        return $this;
    }

    /**
     * Обернуть defineConfig в функцию с параметрами
     *
     * Преобразует: `export default defineConfig({...})`
     * В:           `export default defineConfig(({mode}) => { return {...} })`
     *
     * @param array $params Параметры функции: ['mode'] или ['mode', 'command']
     * @return self
     */
    public function wrapWithDefineConfig(array $params = ['mode']): self
    {
        $content = $this->getContent();

        if ($this->isWrappedWithFunction($content)) {
            return $this;
        }

        $paramStr = implode(', ', $params);
        $content = $this->wrapDefineConfigWithFunction($content, $paramStr);
        $content = $this->addReturnStatementIfNeeded($content, $paramStr);
        $content = $this->fixEndOfFile($content);

        $this->saveContent($content);
        return $this;
    }

    /**
     * Добавить build конфиг
     *
     * @param array $buildConfig Конфиг для build секции
     * @return self
     */
    public function addBuildConfig(array $buildConfig): self
    {
        return $this->addConfigSection('build', $buildConfig);
    }

    /**
     * Добавить server конфиг
     *
     * @param array $serverConfig Конфиг для server секции
     * @return self
     */
    public function addServerConfig(array $serverConfig): self
    {
        return $this->addConfigSection('server', $serverConfig);
    }

    /**
     * Добавить define (переменные окружения)
     *
     * @param array $definitions Определения: ['__VERSION__' => '1.0.0']
     * @return self
     */
    public function addDefine(array $definitions): self
    {
        $content = $this->getContent();

        if (preg_match('/define\s*:\s*{/m', $content)) {
            return $this;
        }

        $defineContent = $this->buildDefineObject($definitions);
        $content = $this->insertConfigSection($content, 'define', $defineContent);

        $this->saveContent($content);
        return $this;
    }

    /**
     * Использовать переменные окружения (loadEnv)
     *
     * Добавляет:
     * - импорт loadEnv из vite
     * - обёртывает defineConfig в функцию
     * - добавляет const env = loadEnv(...)
     *
     * @param string $varName Имя переменной (не используется в текущей версии)
     * @return self
     */
    public function useEnvVariable(string $varName = 'VITE_MODULE_NAME'): self
    {
        $this->addImport('loadEnv', 'vite');

        if (!$this->isWrappedWithFunction($this->getContent())) {
            $this->wrapWithDefineConfig(['mode']);
        }

        $content = $this->getContent();

        if (!preg_match('/const env = loadEnv/m', $content)) {
            $this->injectLoadEnvCall($content);
        }

        return $this;
    }

    /**
     * Найти файл конфигурации (ts или js)
     */
    private function findConfigFile(Directory $directory): File
    {
        $basePath = $directory->getPhysicalPath() . DIRECTORY_SEPARATOR;

        foreach (['vite.config.ts', 'vite.config.js'] as $filename) {
            if (file_exists($basePath . $filename)) {
                return new File($basePath . $filename);
            }
        }

        throw new RuntimeException(
            "vite.config.js or vite.config.ts not found in {$directory->getPhysicalPath()}"
        );
    }

    /**
     * Получить содержимое конфиг файла
     */
    private function getContent(): string
    {
        return $this->config->getContents();
    }

    /**
     * Сохранить содержимое конфиг файла
     */
    private function saveContent(string $content): void
    {
        $this->config->putContents($content);
    }

    /**
     * Распарсить строку импортов в массив элементов
     *
     * 'loadEnv, config' => ['loadEnv', 'config']
     */
    private function parseImportItems(string $what): array
    {
        return array_map('trim', explode(',', $what));
    }

    /**
     * Проверить существует ли импорт
     */
    private function importExists(string $item, string $from, string $content): bool
    {
        $pattern = '/import\s*{[^}]*\b' . preg_quote($item, '/') . '\b[^}]*}\s*from\s*[\'"]'
            . preg_quote($from, '/') . '[\'"];?/';

        return preg_match($pattern, $content) === 1;
    }

    /**
     * Добавить импорт в содержимое
     */
    private function addImportToContent(string $item, string $from, string $content): string
    {
        // Если импорт из этого модуля уже есть - добавить к нему
        if ($this->tryMergeWithExistingImport($item, $from, $content)) {
            return $content;
        }

        // Иначе добавить новый импорт после последнего существующего
        return $this->addNewImport($item, $from, $content);
    }

    /**
     * Попытаться объединить с существующим импортом из этого модуля
     */
    private function tryMergeWithExistingImport(string $item, string $from, &$content): bool
    {
        $existingPattern = '/import\s*{([^}]*)}\s*from\s*[\'"]' . preg_quote($from, '/') . '[\'"];?/';

        if (!preg_match($existingPattern, $content)) {
            return false;
        }

        $newContent = preg_replace_callback(
            $existingPattern,
            function ($matches) use ($item) {
                $existing = trim($matches[1]);
                $merged = $existing . ', ' . $item;
                return 'import { ' . $merged . ' } from \'' . $matches[1] . '\';';
            },
            $content,
            1
        );

        $content = $newContent;
        return true;
    }

    /**
     * Добавить совершенно новый импорт
     */
    private function addNewImport(string $item, string $from, string $content): string
    {
        $lastImportPattern = '/(import\s*{[^}]*}\s*from\s*[\'"][^\'"]*[\'"];?)/';

        if (preg_match_all($lastImportPattern, $content, $matches)) {
            $lastImport = end($matches[0]);
            $newImport = "\nimport { " . $item . " } from '" . $from . "';";
            return str_replace($lastImport, $lastImport . $newImport, $content, 1);
        }

        // Если импортов нет - добавить в начало
        return "import { " . $item . " } from '" . $from . "';\n" . $content;
    }

    /**
     * Объединить экспорты двух импортов
     */
    private function mergeImportExports(string $currentExports, string $additionalExports): string
    {
        $current = array_map('trim', explode(',', $currentExports));
        $additional = array_map('trim', explode(',', $additionalExports));

        foreach ($additional as $export) {
            if (!in_array($export, $current)) {
                $current[] = $export;
            }
        }

        // Получить модуль из оригинального контента
        preg_match("/from\s*['\"]([^'\"]+)['\"]/", $currentExports, $matches);

        return 'import { ' . implode(', ', $current) . ' } from \'' . $current . '\';';
    }

    /**
     * Проверить обёрнут ли defineConfig в функцию
     */
    private function isWrappedWithFunction(string $content): bool
    {
        return preg_match('/defineConfig\s*\(\(\{/m', $content) === 1;
    }

    /**
     * Обернуть defineConfig в функцию
     */
    private function wrapDefineConfigWithFunction(string $content, string $paramStr): string
    {
        $pattern = '/export\s+default\s+defineConfig\s*\(/';
        $replacement = 'export default defineConfig(({' . $paramStr . '}) => ';

        return preg_replace($pattern, $replacement, $content, 1);
    }

    /**
     * Добавить return statement если его нет
     */
    private function addReturnStatementIfNeeded(string $content, string $paramStr): string
    {
        if (preg_match('/return\s+{/m', $content)) {
            return $content;
        }

        $pattern = '/defineConfig\s*\(\(\{' . preg_quote($paramStr) . '\}\)\s*=>\s*\{/m';
        $replacement = 'defineConfig(({' . $paramStr . '}) => { return {';

        return preg_replace($pattern, $replacement, $content, 1);
    }

    /**
     * Исправить конец файла (добавить }) )
     */
    private function fixEndOfFile(string $content): string
    {
        return preg_replace('/}\s*\)\s*$/m', '} })', $content);
    }

    /**
     * Добавить конфиг-секцию (build, server, etc)
     */
    private function addConfigSection(string $sectionName, array $sectionConfig): self
    {
        $content = $this->getContent();

        if (preg_match('/' . $sectionName . '\s*:\s*{/m', $content)) {
            return $this;
        }

        $sectionContent = $this->arrayToJsObject($sectionConfig, 6);
        $content = $this->insertConfigSection($content, $sectionName, $sectionContent);

        $this->saveContent($content);
        return $this;
    }

    /**
     * Вставить конфиг-секцию в подходящее место
     */
    private function insertConfigSection(string $content, string $sectionName, string $sectionContent): string
    {
        // Ищем последний параметр перед }) и добавляем перед ней
        if (preg_match('/([^}]+),?\s*(\n\s*}\s*}\))/m', $content)) {
            return preg_replace(
                '/([^}]+),?\s*(\n\s*}\s*}\))/m',
                '$1,' . "\n      " . $sectionName . ': ' . $sectionContent . '$2',
                $content,
                1
            );
        }

        return $content;
    }

    /**
     * Построить define объект
     */
    private function buildDefineObject(array $definitions): string
    {
        $lines = ["{\n"];

        foreach ($definitions as $key => $value) {
            $lines[] = "        '{$key}': '{$value}',\n";
        }

        $lines[] = "      }";

        return implode('', $lines);
    }

    /**
     * Внедрить вызов loadEnv в функцию
     */
    private function injectLoadEnvCall(string &$content): void
    {
        $pattern = '/export default defineConfig\(\(\{mode\}\) => \{\s*return/';
        $replacement = 'export default defineConfig(({mode}) => { const env = loadEnv(mode, process.cwd(), \'\'); return';

        $content = preg_replace($pattern, $replacement, $content, 1);
        $this->saveContent($content);
    }

    /**
     * Преобразовать PHP массив в JavaScript объект
     *
     * [
     *   'rollupOptions' => [
     *     'output' => ['format' => 'umd']
     *   ]
     * ]
     *
     * становится:
     *
     * {
     *   rollupOptions: {
     *     output: {
     *       format: 'umd',
     *     }
     *   }
     * }
     */
    private function arrayToJsObject(array $array, int $indent = 0): string
    {
        $indentStr = str_repeat(' ', $indent);
        $nextIndentStr = str_repeat(' ', $indent + 2);

        $lines = ["{\n"];

        foreach ($array as $key => $value) {
            $lines[] = $nextIndentStr . $key . ': ';

            if (is_array($value)) {
                $lines[] = $this->arrayToJsObject($value, $indent + 2);
            } elseif (is_string($value) && !is_numeric($value)) {
                $lines[] = "'" . $value . "'";
            } else {
                $lines[] = $value;
            }

            $lines[] = ",\n";
        }

        $lines[] = $indentStr . '}';

        return implode('', $lines);
    }
}