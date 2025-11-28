<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy;

use CopyDirFiles;

use Bitrix\Main\Error;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Result;
use Sholokhov\FrontBoot\App;
use Sholokhov\FrontBoot\Console\Terminal;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class DefaultStrategy implements ExtensionGeneratorInterface
{
    public function __construct()
    {
    }

    public function generate(Directory $directory): Result
    {
        $result = new Result;

        if (!$this->copy($directory)) {
            return $result->addError(new Error('Error generate'));
        }

        return $result;
    }

    /**
     * Копирование файлов расширения
     *
     * @param Directory $directory
     * @return bool
     */
    private function copy(Directory $directory): bool
    {
        $appDir = App::getRootDir();
        return CopyDirFiles(
            $appDir->getPhysicalPath() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'extension',
            $directory->getPhysicalPath(),
            true,
            true
        );
    }

    /**
     * Запустить генерацию
     *
     * @param Directory $directory
     * @param string $name
     * @return Process
     */
    protected function run(Directory $directory, string $name): Process
    {
        $command = $this->getCommand($directory, $name);
        return $this->terminal->command($command);
    }

    /**
     * Создает консольную команду, для запуска установки vue vite
     *
     * @param Directory $directory
     * @param string $name
     * @return string
     */
    protected function getCommand(Directory $directory, string $name): string
    {
        return sprintf(
            "cd %s && yes | npm create vue@latest %s -- --default",
            escapeshellarg($directory->getPhysicalPath()),
            $name
        );
    }
}