<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Console\InteractsWithOutTrait;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Выводит все зарегистрированные расширения
 */
#[AsCommand('extensions', 'List of all registered extensions')]
class ListExtensionsCommand extends Command
{
    use InteractsWithOutTrait;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output->table(
            ['Name', 'Directory', 'Description'],
            array_map(
                fn(array $item) => [$item['ID'], $item['PATH'], $item['DESCRIPTION']],
                ExtensionTable::getList()->fetchAll()
            )
        );

        return self::SUCCESS;
    }
}