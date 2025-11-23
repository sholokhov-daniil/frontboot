<?php

namespace Sholokhov\FrontBoot;

use Throwable;

use Sholokhov\FrontBoot\Loader\AbstractExtensionRegister;
use Sholokhov\FrontBoot\Loader\CoreExtensionsRegister;
use Sholokhov\FrontBoot\Loader\ExtensionRegister;

/**
 * Производит регистрацию всех расширений
 */
class Kernel
{
    /**
     * @var class-string<AbstractExtensionRegister>[]
     */
    private array $registers = [
        CoreExtensionsRegister::class,
        ExtensionRegister::class,
    ];

    /**
     * @return void
     */
    public function run(): void
    {
        try {
            foreach ($this->registers as $entityName) {
                $register = new $entityName;
                $register->run();
            }
        } catch (Throwable $throwable) {
            AddMessage2Log($throwable);
        }
    }
}