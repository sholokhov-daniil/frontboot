<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use CJSCore;

use Exception;
use Sholokhov\FrontBoot\Builder\ConfigurationBuilder;
use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Console\InteractsWithOutTrait;

use Sholokhov\FrontBoot\Validator\ExtensionNameValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выполняющая регистрацию нового расширения
 */
#[AsCommand('registration', 'Registering an extension', ['reg'])]
class RegistrationCommand extends Command
{
    use InteractsWithOutTrait;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        do {
            $name = (string)$this->ask('Extension name');

            if (!ExtensionNameValidator::validate($name)) {
                $this->warning('Extension name is invalid');
                $name = '';
            }
        } while (!$name);

        do {
            $path = (string)$this->ask('The path to the root folder of the extension' . PHP_EOL . ' Example: /var/www/web.ru/js/my_extenstion_folder');
        } while (!$path);

        $description = $this->ask('Extension description');

        if (!is_dir($path)) {
            $this->error('The directory was not found');
            return self::FAILURE;
        }

        $extension = ConfigurationBuilder::create($path);

        if ($extension === null) {
            $this->error('The configuration file is missing');
            return self::FAILURE;
        }

        if (CJSCore::IsExtRegistered($name)) {
            $this->error('The extension has already been registered');
            return self::FAILURE;
        }

        $result = ExtensionTable::add([
            'ID' => $name,
            'PATH' => $path,
            'DESCRIPTION' => $description,
        ]);

        if (!$result->isSuccess()) {
            array_map(
                fn (string $message) => $this->error($message),
                $result->getErrorMessages()
            );

            return self::FAILURE;
        }

        $this->frame([
            " Success!",
            " Extension $name registered",
            "",
            " Include extension in php",
            " CJSCore::Init(['$name']);",
            "",
            " Include extension in js",
            " BX.loadExt('$name').then(() => {\n     // The code after loading\n });",
            "",
            " Extension Directory",
            " $path"
        ]);

        return self::SUCCESS;
    }
}