<?php

namespace Sholokhov\FrontBoot\Builder;

use Sholokhov\FrontBoot\Config;
use Sholokhov\FrontBoot\Locator\AbstractLocator;

/**
 * Создает конфигурационный файл расширения
 */
class ConfigurationBuilder
{
    /**
     * Создание объекта
     *
     * @param string $directory
     * @return Config|null
     */
    public static function create(string $directory): Config|null
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $optionPath = AbstractLocator::getOption($directory);

        if (!file_exists($optionPath)) {
            return null;
        }

        return @include $optionPath;
    }
}