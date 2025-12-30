<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy\Vue;

use RuntimeException;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

/**
 * Модифицирует ядро js приложения
 */
class VueCoreModifier
{
    private File $file;

    public function __construct(Directory $directory)
    {
        $this->file = $this->findFile($directory);
    }

    /**
     * Модифицировать ядро js приложения
     *
     * @return $this
     */
    public function modify(): self
    {
        $this->createBackup();
        $content = file_get_contents($this->file->getPath());

        if (str_contains($content, 'FrontBoot.extensions.set(')) {
            return $this;
        }

        // Пробуем разные шаблоны
        if ($this->hasStandardTemplate($content)) {
            $content = $this->replaceStandardTemplate($content);
        } elseif ($this->hasSimpleTemplate($content)) {
            $content = $this->replaceSimpleTemplate($content);
        } elseif ($this->hasInlineTemplate($content)) {
            $content = $this->replaceInlineTemplate($content);
        } else {
            // Если не нашли стандартные шаблоны, пытаемся найти и заменить любым способом
            $content = $this->replaceGenericAppCreation($content);
        }

        file_put_contents($this->file->getPath(), $content);
        return $this;
    }

    /**
     * Проверяет, есть ли стандартный шаблон с const app = createApp(App)
     *
     * @param string $content
     * @return bool
     */
    private function hasStandardTemplate(string $content): bool
    {
        return preg_match('/const\s+app\s*=\s*createApp\(App\)/', $content) &&
            preg_match('/app\.mount\([\'"]?#app[\'"]?\)/', $content);
    }

    /**
     * Проверяет, есть ли простой шаблон (без use вызовов)
     *
     * @param string $content
     * @return bool
     */
    private function hasSimpleTemplate(string $content): bool
    {
        // Шаблон без промежуточных use вызовов
        return preg_match('/const\s+app\s*=\s*createApp\(App\)\s*\n\s*app\.mount\([\'"]?#app[\'"]?\)/s', $content);
    }

    /**
     * Проверяет, есть ли inline шаблон (createApp(App).mount('#app'))
     *
     * @param string $content
     * @return bool
     */
    private function hasInlineTemplate(string $content): bool
    {
        return preg_match('/createApp\s*\(\s*App\s*\)\s*\.\s*mount\s*\(\s*[\'"]?#app[\'"]?\s*\)/', $content);
    }

    /**
     * Заменяет стандартный шаблон (с use вызовами)
     *
     * @param string $content
     * @return string
     */
    private function replaceStandardTemplate(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $foundAppBlock = false;
        $useStatements = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Нашли создание приложения
            if (str_contains($trimmed, 'const app = createApp(App)')) {
                $foundAppBlock = true;
                continue; // Пропускаем эту строку
            }

            // Если мы в блоке приложения, собираем use вызовы
            if ($foundAppBlock) {
                // Нашли use вызов
                if (preg_match('/app\.use\(([^)]+)\)/', $trimmed)) {
                    $useStatements[] = $trimmed;
                    continue;
                }

                // Нашли mount - конец блока
                if (preg_match('/app\.mount\([\'"]?#app[\'"]?\)/', $trimmed)) {
                    // Генерируем новый блок
                    $frontbootBlock = $this->generateFrontbootBlockFromStatements($useStatements);

                    // Добавляем новый блок в результат
                    $frontbootLines = explode("\n", $frontbootBlock);
                    foreach ($frontbootLines as $fbLine) {
                        $result[] = $fbLine;
                    }

                    $foundAppBlock = false;
                    $useStatements = [];
                    continue; // Пропускаем строку с mount
                }

                // Пропускаем пустые строки в блоке
                if ($trimmed === '') {
                    continue;
                }

                // Если есть другие строки в блоке (не use и не mount), сохраняем их
                $result[] = $line;
            } else {
                // Вне блока приложения - просто копируем строку
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Заменяет простой шаблон (без use вызовов)
     *
     * @param string $content
     * @return string
     */
    private function replaceSimpleTemplate(string $content): string
    {
        $pattern = '/(const\s+app\s*=\s*createApp\(App\))\s*\n\s*(app\.mount\([\'"]?#app[\'"]?\))/';

        if (preg_match($pattern, $content, $matches)) {
            $frontbootBlock = $this->generateFrontbootBlockFromStatements([]);
            return preg_replace($pattern, $frontbootBlock, $content, 1);
        }

        return $content;
    }

    /**
     * Заменяет inline шаблон (createApp(App).mount('#app'))
     *
     * @param string $content
     * @return string
     */
    private function replaceInlineTemplate(string $content): string
    {
        $pattern = '/(createApp\s*\(\s*App\s*\)\s*\.\s*mount\s*\(\s*[\'"]?#app[\'"]?\s*\))/';

        if (preg_match($pattern, $content, $matches)) {
            $frontbootBlock = $this->generateFrontbootBlockFromStatements([]);
            return preg_replace($pattern, $frontbootBlock, $content, 1);
        }

        return $content;
    }

    /**
     * Общий метод замены для любых форматов создания приложения
     *
     * @param string $content
     * @return string
     */
    private function replaceGenericAppCreation(string $content): string
    {
        // Ищем любой вызов createApp
        $pattern = '/(createApp\s*\(\s*App\s*(?:,\s*[^)]+)?\s*\).*?mount\s*\(\s*[\'"]?#app[\'"]?\s*\))/s';

        if (preg_match($pattern, $content, $matches)) {
            $oldBlock = $matches[1];

            // Пытаемся извлечь use вызовы из блока
            $useStatements = [];
            if (preg_match_all('/app\.use\(([^)]+)\)/', $oldBlock, $useMatches)) {
                foreach ($useMatches[0] as $useCall) {
                    $useStatements[] = $useCall;
                }
            }

            $frontbootBlock = $this->generateFrontbootBlockFromStatements($useStatements);
            return str_replace($oldBlock, $frontbootBlock, $content);
        }

        return $content;
    }

    /**
     * Генерирует блок FrontBoot из собранных useStatements
     *
     * @param array $useStatements
     * @return string
     */
    private function generateFrontbootBlockFromStatements(array $useStatements): string
    {
        $formattedUses = '';
        if (!empty($useStatements)) {
            foreach ($useStatements as $statement) {
                // Форматируем вызов, добавляя правильные отступы
                $formattedUses .= "      " . trim($statement) . "\n";
            }
            // Добавляем пустую строку после use вызовов, если они есть
            $formattedUses .= "\n";
        }

        return <<<JS
FrontBoot.extensions.set(
  import.meta.env.VITE_EXTENSION_ID,
  {
    app: null,

    mount(node, data) {
      this.unmount();

      const app = createApp(App, data);
{$formattedUses}
      app.mount(node);
      
      return this.app = app;
    },

    unmount() {
      this.app?.unmount();
    }
  }
);
JS;
    }

    /**
     * Находит main.js или main.ts файл
     *
     * @param Directory $directory
     * @return File
     */
    private function findFile(Directory $directory): File
    {
        $srcPath = $directory->getPhysicalPath() . DIRECTORY_SEPARATOR . 'src';

        if (!is_dir($srcPath)) {
            throw new RuntimeException("Directory 'src' not found in project");
        }

        $files = [
            $srcPath . DIRECTORY_SEPARATOR . 'main.js',
            $srcPath . DIRECTORY_SEPARATOR . 'main.ts',
            $srcPath . DIRECTORY_SEPARATOR . 'main.jsx',
            $srcPath . DIRECTORY_SEPARATOR . 'main.tsx',
        ];

        foreach ($files as $filePath) {
            if (file_exists($filePath)) {
                return new File($filePath);
            }
        }

        throw new RuntimeException("main.js/main.ts not found in src directory");
    }

    /**
     * Создает бекап ядра
     *
     * @return void
     */
    private function createBackup(): void
    {
        $backupPath = $this->file->getPath() . '.backup';
        copy($this->file->getPath(), $backupPath);
    }
}