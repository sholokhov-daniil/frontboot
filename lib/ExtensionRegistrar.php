<?php

namespace Sholokhov\FrontBoot;

use CJSCore;
use Sholokhov\FrontBoot\Builder\ConfigurationBuilder;

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
            $extension = ConfigurationBuilder::create($directory);

            if (!$extension) {
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