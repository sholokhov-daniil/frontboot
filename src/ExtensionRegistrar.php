<?php

namespace Sholokhov\FrontBoot;

use CJSCore;
use Sholokhov\FrontBoot\Locator\AbstractLocator;

class ExtensionRegistrar
{
    /**
     * Запустить регистрацию расширений
     *
     * @param string[] $extensions
     * @return void
     */
    public static function run(array $extensions): void
    {
        foreach ($extensions as $id => $directory) {
            $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $optionPath = AbstractLocator::getOption($directory);

            if (!file_exists($optionPath)) {
                return;
            }

            $extension = @include $optionPath;

            if (!($extension instanceof Extension)) {
                return;
            }

            if (CJSCore::isExtensionLoaded($id)) {
                return;
            }

            CJSCore::RegisterExt(
                $id,
                [
                    'js' => $extension->js,
                    'css' => $extension->css,
                    'lang' => $extension->lang,
                    'rel' => $extension->rel,
                    'skip_core' => $extension->skipCore,
                    'use' => $extension->use,
                ]
            );
        }
    }
}