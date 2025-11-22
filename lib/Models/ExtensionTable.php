<?php

namespace Sholokhov\FrontBoot\Models;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Хранилище зарегистрированных расширений
 */
class ExtensionTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'sholokhov_frontboot_extensions';
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            (new Fields\StringField('ID'))
                ->configureRequired()
                ->configurePrimary(),

            (new Fields\TextField('PATH'))
                ->configureRequired()
                ->configureUnique(),
        ];
    }
}