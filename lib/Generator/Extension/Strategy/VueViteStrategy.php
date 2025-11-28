<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy;

use Sholokhov\FrontBoot\Console\Terminal;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\IO\Directory;

use Symfony\Component\Process\Process;

/**
 * Генерирует Vue vite расширение
 */
class VueViteStrategy implements ExtensionGeneratorInterface
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
     */
    public function generate(Directory $directory): Result
    {
        $result = new Result;

        $process = $this->run($directory);

        if ($process->getStatus() === Process::ERR) {
            return $result->addError(new Error('Error installed: ' . $process->getErrorOutput()));
        }

        return $result;
    }

    /**
     * Запустить генерацию
     *
     * @param Directory $directory
     * @return Process
     */
    protected function run(Directory $directory): Process
    {
        $command = $this->getCommand($directory);
        return $this->terminal->command($command);
    }

    /**
     * Создает консольную команду, для запуска установки vue vite
     *
     * @param Directory $directory
     * @return string
     */
    protected function getCommand(Directory $directory): string
    {
        return sprintf(
            "cd %s && yes | npm create vue@latest %s -- --default",
            escapeshellarg(dirname($directory->getPhysicalPath())),
            $directory->getName()
        );
    }
}