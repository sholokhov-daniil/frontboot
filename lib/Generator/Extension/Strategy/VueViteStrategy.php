<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy;

use Bitrix\Main\Error;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Result;
use Sholokhov\FrontBoot\Console\Terminal;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class VueViteStrategy implements ExtensionGeneratorInterface
{
    protected readonly Terminal $terminal;

    public function __construct()
    {
        $this->terminal = new Terminal;
    }

    public function generate(Directory $directory, string $name): Result
    {
        $result = new Result;

        $process = $this->run($directory, $name);

        if ($process->getStatus() === Process::ERR) {
            return $result->addError(new Error('Error installed: ' . $process->getErrorOutput()));
        }

        return $result;
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