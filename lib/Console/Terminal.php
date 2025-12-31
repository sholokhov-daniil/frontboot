<?php

namespace Sholokhov\FrontBoot\Console;

use Symfony\Component\Process\Process;


class Terminal
{
    /**
     * Ожидать окончания выполнения команды
     *
     * @var bool
     */
    private bool $wait = true;

    /**
     * Время ожидания ответа
     * 
     * @var int
     */
    private int $timeout = 600;

    /**
     * Выполнение команды.
     *
     * @param string $command
     * @return Process
     */
    public function command(string $command): Process
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout($this->timeout);

        // if (Process::isTtySupported()) {
            // $process->setTty(true);
        // }

        $process->run();

        if ($this->wait) {
            while ($process->isRunning());
        }

        return $process;
    }

    /**
     * Ожидать ответ выполнения команды
     *
     * @param bool $wait
     * @return $this
     */
    public function setWait(bool $wait): self
    {
        $this->wait = $wait;
        return $this;
    }

    /**
     * Время ожидания ответа
     * 
     * @param int $timeout
     * @return Terminal
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }
}