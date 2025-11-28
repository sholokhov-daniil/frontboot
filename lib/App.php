<?php

namespace Sholokhov\FrontBoot;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\IO\Directory;

class App
{
    /**
     * ID модуля
     *
     * @return string
     */
    public static function getId(): string
    {
        return 'sholokhov.frontboot';
    }

    /**
     * Возвращает основную директорию js расширений
     *
     * @return Directory|null
     */
    public static function getJsDirectory(): Directory|null
    {
        $path = Application::getDocumentRoot() . '/local/frontboot/';
        $directory = new Directory($path);

        if (!$directory->isExists()) {
            $directory->create();

            if (!$directory->isExists()) {
                return null;
            }
        }

        return $directory;
    }

    /**
     * Конфигурация модуля
     *
     * @return Configuration
     */
    public static function config(): Configuration
    {
        return Configuration::getInstance(self::getId());
    }
}