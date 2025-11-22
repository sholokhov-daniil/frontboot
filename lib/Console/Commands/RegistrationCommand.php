<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use CJSCore;

use Exception;
use Sholokhov\FrontBoot\Builder\ConfigurationBuilder;
use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Console\InteractsWithOutTrait;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выполняющая регистрацию нового расширения
 */
#[AsCommand('registration', 'Регистрация расширения', ['reg'])]
class RegistrationCommand extends Command
{
    use InteractsWithOutTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            'id',
            'i',
            InputOption::VALUE_REQUIRED,
            'Идентификатор расширения'
        );

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Путь до корневой папки расширения'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (string)$input->getOption('id');
        $path = (string)$input->getOption('path');

        if (!is_dir($path)) {
            $this->error('The directory was not found');
            return self::FAILURE;
        }

        $extension = ConfigurationBuilder::create($path);

        if ($extension === null) {
            $this->error('The configuration file is missing');
            return self::FAILURE;
        }

        if (CJSCore::IsExtRegistered($id)) {
            $this->error('The extension has already been registered');
            return self::FAILURE;
        }

        $result = ExtensionTable::add([
            'ID' => $id,
            'PATH' => $path,
        ]);

        if (!$result->isSuccess()) {
            array_map(
                fn (string $message) => $this->error($message),
                $result->getErrorMessages()
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}