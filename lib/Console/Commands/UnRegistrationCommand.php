<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use Exception;

use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Console\InteractsWithOutTrait;

use Bitrix\Main\IO\Directory;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выполняющая разрегистрацию существующего расширения
 */
#[AsCommand('unregistration', 'Разрегистрация расширения', ['unreg'])]
class UnRegistrationCommand extends Command
{
    use InteractsWithOutTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument(
            'id',
            InputArgument::REQUIRED,
            'Идентификатор расширения'
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
        $id = (string)$input->getArgument('id');

        $confirm = $this->confirm('Unregister the extension?', false);

        if (!$confirm) {
            $this->info('Unregistration cancelled');
            return self::SUCCESS;
        }

        $deleteFiles = $this->confirm('Delete all extension files? (the operation is irreversible)', false);
        if ($deleteFiles) {
            $deleteFiles = $this->confirm('Are you sure you want to delete the extension files?', true);
        }

        $extension = [];
        if ($deleteFiles) {
            $extension = ExtensionTable::getById($id)->fetch();

            if (!$extension) {
                $this->error('Extension not found');
                return self::FAILURE;
            }
        }

        $result = ExtensionTable::delete($id);

        if ($result->isSuccess() === false) {
            array_map(
                fn (string $message) => $this->error($message),
                $result->getErrorMessages()
            );

            return self::FAILURE;
        }

        if ($extension) {
            Directory::deleteDirectory($extension['PATH']);

            if (Directory::isDirectoryExists($extension['PATH'])) {
                $this->warning("Couldn't delete extension files along the way: {$extension['PATH']}");
            }
        }

        $this->success('The extension is unregistered');

        return self::SUCCESS;
    }
}