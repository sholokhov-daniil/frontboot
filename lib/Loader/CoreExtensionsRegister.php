<?php

namespace Sholokhov\FrontBoot\Loader;

use Bitrix\Main\Config\Configuration;

/**
 * Регистрирует системные расширения
 */
class CoreExtensionsRegister extends AbstractExtensionRegister
{

    protected function getSource(): iterable
    {
        $result = [];
        $iterator = Configuration::getInstance('sholokhov.frontboot')->get('extensions') ?: [];

        foreach ($iterator as $key => $value) {
            $result[] = [
                'ID' => $key,
                'PATH' => $value,
            ];
        }

        return $result;
    }
}