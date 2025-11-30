<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Sholokhov\FrontBoot\App;
use Sholokhov\FrontBoot\Console\Terminal;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\IO\Directory;

use Symfony\Component\Process\Process;

/**
 * Генерирует Vue vite расширение
 */
class ViteVueStrategy implements ExtensionGeneratorInterface
{
    /**
     * @var Terminal
     */
    protected readonly Terminal $terminal;

    public function __construct()
    {
        $this->terminal = new Terminal;
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

        if (!$this->configuration($directory)) {
            return $result->addError(new Error('No configuration found'));
        }

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
     * Запустить генерацию
     *
     * @param Directory $directory
     * @return Process
     */
    private function run(Directory $directory): Process
    {
        $command = $this->getCommand($directory);
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
     * Создает консольную команду, для запуска установки vue vite
     *
     * @param Directory $directory
     * @return string
     */
    private function getCommand(Directory $directory): string
    {
        return sprintf(
            "cd %s && yes | npm create vue@latest %s -- --default",
            escapeshellarg(dirname($directory->getPhysicalPath())),
            $directory->getName()
        );
    }
}