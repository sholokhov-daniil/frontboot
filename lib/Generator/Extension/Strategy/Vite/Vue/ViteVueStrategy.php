<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy\Vite\Vue;

use Bitrix\Main\Diag\Debug;
use Exception;

use Sholokhov\FrontBoot\App;
use Sholokhov\FrontBoot\Console\Terminal;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Sholokhov\Frontboot\Generator\Extension\Strategy\Vite\ViteConfigModifier;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Генерирует Vue vite расширение
 */
class ViteVueStrategy implements ExtensionGeneratorInterface
{
    /**
     * @var Terminal
     */
    protected readonly Terminal $terminal;

    /**
     * Вывод информации в консоль
     * 
     * @var SymfonyStyle|null
     */
    protected readonly SymfonyStyle $output;

    /**
     * Ручная настройка vue
     *
     * @var boolean
     */
    protected bool $manual = false;

    public function __construct(SymfonyStyle $output)
    {
        $this->terminal = new Terminal;
        $this->output = $output;
    }

    /**
     * @param Directory $directory
     * @return Result
     * @throws FileNotFoundException
     */
    public function generate(Directory $directory): Result
    {
        $result = new Result;

        $process = $this->run($directory);

        if ($process->getStatus() === Process::ERR) {
            return $result->addError(new Error('Error installed: ' . $process->getErrorOutput()));
        }

        $this->configuration($directory);
        $this->modifiyConfig($directory);

        $result->setData([
            "",
            "Appeal to the extension",
            "FrontBoot.extensions.get('{$directory->getName()}')",
            "",
            "Go to the directory and run the command:",
            "npm install && npm run build"
        ]);

        return $result;
    }

    /**
     * Модифицировать конфигурацию vite
     *
     * @param Directory $directory
     * @return void
     * @throws FileNotFoundException
     */
    private function modifiyConfig(Directory $directory): void
    {
        (new ViteConfigModifier($directory))->modify();
    }

    /**
     * Запустить генерацию
     *
     * @param Directory $directory
     * @return Process
     * @throws Exception
     */
    private function run(Directory $directory): Process
    {
        $version = $this->getVersion();
        $features = $this->getFeatures();

        $builder = new ViteVueCommandBuilder($directory);

        $builder->setFeatures($features);
        $builder->setVersion($version);
        $builder->setProjectName($directory->getName());

        $command = $builder->create();

        return $this->terminal->command($command);
    }

    /**
     * Производим конфигурацию vue приложения
     *
     * @param Directory $directory
     * @return bool
     * @throws FileNotFoundException
     */
    private function configuration(Directory $directory): bool
    {
        if (!$this->copy($directory)) {
            return false;
        }

        $variables = [
            '#EXTENSION_ID#' => $directory->getName()
        ];

        return $this->replaceVariables($directory, $variables);
    }

    /**
     * Копирование конфигурационных файлов в расширение
     *
     * @param Directory $directory
     * @return bool
     */
    private function copy(Directory $directory): bool
    {
        $appDir = App::getRootDir();

        Debug::dumpToFile([
            'F' => $appDir->getPhysicalPath() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'vite-vue',
            'T' => $directory->getPhysicalPath()
        ]);

        return CopyDirFiles(
            $appDir->getPhysicalPath() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'vite-vue',
            $directory->getPhysicalPath(),
            true,
            true
        );
    }

    /**
     * Подменяет системные переменные
     *
     * @param Directory $directory
     * @param array $variables
     * @return bool
     * @throws FileNotFoundException
     */
    private function replaceVariables(Directory $directory, array $variables): bool
    {
        $path = $directory->getPhysicalPath() . DIRECTORY_SEPARATOR . '.env';
        $env = new File($path);

        if (!$env->isExists()) {
            return false;
        }

        $content = $env->getContents();
        $content = str_replace(array_keys($variables), array_values($variables), $content);

        return $env->putContents($content);
    }

    /**
     * Список устанавливаемых расширений
     * 
     * @return Feature[]
     */
    private function getFeatures(): array
    {
        $default = $this->output->confirm(
            'Should I use the default settings?',
            true
        );

        if ($default) {
            return [Feature::Default];
        }

        foreach (Feature::cases() as $item) {
            if ($item->value === 'default') {
                continue;
            }

            $features[$item->value] = $item->getDescription();
        }

        $choice = $this->output->choice(
            'Select features to include in your project: (Specify it separated by commas)',
            $features,
            null,
            true
        );

        return array_map(
            static function (string $name) {
                $feature = Feature::tryFrom($name);
                if (!$feature) {
                    throw new Exception("Feature '$name' not supported");
                }

                return $feature;
            },
            $choice
        );
    }

    /**
     * Версия проекта
     * 
     * @return string
     */
    private function getVersion(): string
    {
        return (string)$this->output->ask('Version: ', 'latest');
    }
}
