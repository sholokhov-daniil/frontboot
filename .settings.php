<?php

return [
    'commands' => [
        'value' => [
            \Sholokhov\FrontBoot\Console\Commands\RegistrationCommand::class,
            \Sholokhov\FrontBoot\Console\Commands\CreateCommand::class,
            \Sholokhov\FrontBoot\Console\Commands\UnRegistrationCommand::class,
            \Sholokhov\FrontBoot\Console\Commands\ListExtensionsCommand::class,
        ],
        'readonly' => true,
    ],
    'extension-generators' => [
        'value' => [
            'clean' => \Sholokhov\FrontBoot\Generator\Extension\Strategy\DefaultStrategy::class,
            'vue vite' => \Sholokhov\FrontBoot\Generator\Extension\Strategy\VueViteStrategy::class,
        ],
        'readonly' => true,
    ],
    'extensions' => [
        'value' => [
            'frontboot.core' => __DIR__ . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'core',
        ],
        'readonly' => true,
    ],
];