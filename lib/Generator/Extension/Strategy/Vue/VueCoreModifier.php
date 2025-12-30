<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy\Vue;

use Bitrix\Main\IO\Directory;
use RuntimeException;

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

        file_put_contents($this->file->getPath(), implode("\n", $result));
        return $this;
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
        foreach ($useStatements as $statement) {
            // Форматируем вызов, добавляя правильные отступы
            $formattedUses .= "      " . $statement . "\n";
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
            $srcPath . '/main.js',
            $srcPath . '/main.ts',
            $srcPath . '/main.jsx',
            $srcPath . '/main.tsx',
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