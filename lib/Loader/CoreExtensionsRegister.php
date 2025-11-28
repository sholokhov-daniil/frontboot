<?php

namespace Sholokhov\FrontBoot\Loader;

use Sholokhov\FrontBoot\App;

/**
 * Регистрирует системные расширения
 */
class CoreExtensionsRegister extends AbstractExtensionRegister
{

    protected function getSource(): iterable
    {
        $result = [];
        $iterator = App::config()->get('extensions') ?: [];

        foreach ($iterator as $key => $value) {
            $result[] = [
                'ID' => $key,
                'PATH' => $value,
            ];
        }

        return $result;
    }
}