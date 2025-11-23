<?php

namespace Sholokhov\FrontBoot\Loader;

use CJSCore;
use Throwable;

use Sholokhov\FrontBoot\Config;
use Sholokhov\FrontBoot\Builder\ConfigurationBuilder;

/**
 * @internal
 */
abstract class AbstractExtensionRegister
{
    /**
     * Список регистрируемых расширений
     *
     * @return iterable
     */
    abstract protected function getSource(): iterable;

    /**
     * Запустить регистрация
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $iterator = $this->getSource();

            foreach ($iterator as $extension) {
                if (CJSCore::isExtensionLoaded($extension['ID'])) {
                    continue;
                }

                $config = ConfigurationBuilder::create($extension['PATH']);

                if ($config === null) {
                    continue;
                }

                $this->registration($extension['ID'], $config);

                if ($config->autoload) {
                    $autoload[] = $extension['ID'];
                }
            }

            if (!empty($autoload)) {
                CJSCore::Init($autoload);
            }
        } catch (Throwable $throwable) {
            AddMessage2Log($throwable);
        }
    }

    /**
     * Регистрация расширения
     *
     * @param string $id
     * @param Config $config
     * @return void
     */
    protected function registration(string $id, Config $config): void
    {
        CJSCore::RegisterExt(
            $id,
            [
                'js' => $config->js,
                'css' => $config->css,
                'lang' => $config->lang,
                'rel' => $config->rel,
                'skip_core' => $config->skipCore,
                'use' => $config->use,
            ]
        );
    }
}