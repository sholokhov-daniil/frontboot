<?php

namespace Sholokhov\FrontBoot;

use CJSCore;

use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Builder\ConfigurationBuilder;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Производит регистрацию всех расширений
 */
class Kernel
{
    /**
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function run(): void
    {
        $iterator = $this->getExtensions();
        $autoload = [];

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
    }

    /**
     * @param string $id
     * @param Config $config
     * @return void
     */
    private function registration(string $id, Config $config): void
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

    /**
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getExtensions(): array
    {
        return ExtensionTable::query()
            ->addSelect('ID')
            ->addSelect('PATH')
            ->setCacheTtl(36000000)
            ->exec()
            ->fetchAll();
    }
}