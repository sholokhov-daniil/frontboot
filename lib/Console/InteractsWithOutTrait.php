<?php

namespace Sholokhov\FrontBoot\Console;

use Stringable;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithOutTrait
{
    private int $fontWeight = OutputInterface::OUTPUT_NORMAL;

    /**
     * Реализация вывода.
     *
     * @var SymfonyStyle
     */
    protected SymfonyStyle $output;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->setOutput(new SymfonyStyle($input, $output));
    }

    /**
     * Подтвердите вопрос пользователю.
     *
     * @param string $question
     * @param bool $default
     * @return bool
     */
    public function confirm(string $question, bool $default): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Запросить у пользователя ввод данных.
     *
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     * @return mixed
     */
    public function ask(string $question, string $default = null, callable $validator = null): mixed
    {
        return $this->output->ask($question, $default, $validator);
    }

    /**
     * @param string|Stringable|iterable $message
     * @param int $minWidth
     * @return void
     */
    public function frame(string|Stringable|iterable $message, int $minWidth = 0): void
    {
        if (!is_iterable($message)) {
            $message = [(string)$message];
        }

        // Шаг 1: формируем финальный массив строк для рамки, включая переносы и отступы
        $frameLines = [];
        foreach ($message as $line) {
            // Разбиваем по любому переносу
            foreach (preg_split('/\r\n|\r|\n/', $line) as $sub) {
                // Конвертируем табы в пробелы для визуальной стабильности
                $frameLines[] = str_replace("\t", "    ", $sub);
            }
        }

        // Шаг 2: ищем максимальную длину (учитываются отступы!)
        $maxLen = $minWidth;
        foreach ($frameLines as $line) {
            $maxLen = max($maxLen, strlen($line));
        }

        $border = str_repeat('-', $maxLen + 2);

        $this->output->writeln('');
        $this->output->writeln('+' . $border . '+');
        foreach ($frameLines as $line) {
            $this->output->writeln('| ' . str_pad($line, $maxLen) . ' |');
        }
        $this->output->writeln('+' . $border . '+');
        $this->output->writeln('');
    }

    /**
     * Запросить у пользователя ввод, но скрыть ответ.
     * Может подойти для ввода пароля или иной приватной информации.
     *
     * @param string $question
     * @param callable|null $validator
     * @return mixed
     */
    public function secret(string $question, callable $validator = null): mixed
    {
        return $this->output->askHidden($question, $validator);
    }

    /**
     * Вывод строки.
     *
     * @param string|iterable $messages
     * @param string|null $style
     * @param int|null $weight
     * @return void
     */
    public function line(string|iterable $messages, string $style = null, int $weight = null): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $value) {
                $this->line($value, $style, $weight);
            }
        } else {
            $line = $style ? sprintf('<%s>%s</%s>', $style, $messages, $style) : $messages;
            $this->output->writeln($line, $weight ?: $this->fontWeight);
        }
    }

    /**
     * Вывод информационного сообщения.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function info(string|iterable $messages, int $weight = null): void
    {
        $this->line($messages, 'info', $weight);
    }

    /**
     * Вывод текста в виде комментария.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function comment(string|iterable $messages, int $weight = null): void
    {
        $this->line($messages, 'comment', $weight);
    }

    /**
     * Вывод вопроса.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function question(string|iterable $messages, int $weight = null): void
    {
        $this->line($messages, 'question', $weight);
    }

    /**
     * Вывод успеха.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function success(string|iterable $messages, int $weight = null): void
    {
        $this->line($messages, 'fg=black;bg=green', $weight);
    }

    /**
     * Вывод ошибки.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function error(string|iterable $messages, int $weight = null): void
    {
        $this->line($messages, 'error', $weight);
    }

    /**
     * Вывод предупреждения.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function warning(string|iterable $messages, int $weight = null): void
    {
        if ($this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($messages, 'warning', $weight);
    }

    /**
     * Вывод критической информации.
     *
     * @param string|iterable $messages
     * @param int|null $weight
     * @return void
     */
    public function alert(string|iterable $messages, int $weight = null): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $msg) {
                $this->alert($msg, $weight);
            }
        } else {
            $length = mb_strlen(strip_tags($messages)) + 12;
            $this->comment(str_repeat('*', $length), $weight);
            $this->comment('*     ' . $messages . '     *', $weight);
            $this->comment(str_repeat('*', $length), $weight);

            $this->comment('', $weight);
        }
    }

    /**
     * Перевод на новую строку.
     *
     * @param int $count
     * @return void
     */
    public function newLine(int $count = 1): void
    {
        $this->output->newLine($count);
    }

    /**
     * Указание механизма вывода.
     *
     * @param SymfonyStyle $output
     * @return void
     */
    public function setOutput(SymfonyStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * Получение механизма вывода.
     *
     * @return SymfonyStyle
     */
    public function getOutput(): SymfonyStyle
    {
        return $this->output;
    }
}