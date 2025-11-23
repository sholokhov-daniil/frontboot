<?php

namespace Sholokhov\FrontBoot\Loader;

use Sholokhov\FrontBoot\Models\ExtensionTable;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Регистрирует пользовательские расширения
 */
class ExtensionRegister extends AbstractExtensionRegister
{
    /**
     * @return array[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getSource(): iterable
    {
        return ExtensionTable::query()
            ->addSelect('ID')
            ->addSelect('PATH')
            ->setCacheTtl(36000000)
            ->exec()
            ->fetchAll();
    }
}