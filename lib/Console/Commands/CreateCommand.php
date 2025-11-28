<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use Bitrix\Main\Diag\Debug;
use CJSCore;
use Sholokhov\FrontBoot\App;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;
use Sholokhov\FrontBoot\Generator\Extension\GeneratorFactory;
use Sholokhov\FrontBoot\Validator\ExtensionNameValidator;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Throwable;
use ErrorException;

use Sholokhov\FrontBoot\Models\ExtensionTable;
use Sholokhov\FrontBoot\Console\InteractsWithOutTrait;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выполняющая создание нового расширения
 */
#[AsCommand('create', 'Creating a new extension')]
class CreateCommand extends Command
{
    use InteractsWithOutTrait;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ErrorException
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $this->askName();
        $description = (string)$this->ask('Description');
        $extensionDir = null;

        try {
            // TODO: Добавить резервирование
            if (CJSCore::IsExtRegistered($name)) {
                $this->error('The extension has already been registered');
                return self::FAILURE;
            }

            $directory = App::getJsDirectory();
            if (!$directory) {
                $this->error('Error creating a directory with extensions');
                return self::FAILURE;
            }

            $extensionDir = $this->createExtensionDirectory($directory, $name);

            $type = $this->choiceType();
            $generator = $this->createGenerator($type);

            if (!$generator) {
                $this->error('Installer not found');
                $extensionDir->delete();
                return self::FAILURE;
            }

            $this->info([
                sprintf('The generation of the %s extension has been started', $type),
                'It may take some time...'
            ]);

            $result = $generator->generate($directory, $name);

            if (!$result->isSuccess()) {
                $this->error($result->getErrorMessages());
                $extensionDir->delete();
                return self::FAILURE;
            }

            $result = ExtensionTable::add([
                'ID' => $name,
                'PATH' => $extensionDir->getPath(),
                'DESCRIPTION' => $description,
            ]);

            if (!$result->isSuccess()) {
                $this->error($result->getErrorMessages());
                $extensionDir->delete();

                return self::FAILURE;
            }

        } catch (Throwable $exception) {
            $extensionDir?->delete();
            throw $exception;
        }


        $this->frame([
            " Success!",
            " Extension $name created",
            "",
            " Include extension in php",
            " CJSCore::Init(['$name']);",
            "",
            " Include extension in js",
            " BX.loadExt('$name').then(() => {\n     // The code after loading\n });",
            "",
            " Extension Directory",
            " {$extensionDir->getPath()}"
        ]);

        return self::SUCCESS;
    }

    /**
     * Спрашиваем наименование расширения
     *
     * @return string
     */
    private function askName(): string
    {
        do {
            $name = (string)$this->ask('Extension name');

            if (!ExtensionNameValidator::validate($name)) {
                $this->error('Extension name is invalid');
                $name = '';
            }
        } while (!mb_strlen($name));

        return $name;
    }

    /**
     * Пользователь выбирает тип расширения
     *
     * @return string
     */
    private function choiceType(): string
    {
        return (string)$this->output->choice(
            "Тип установки",
            [
                'clean',
                'vue vite'
            ],
            'clean'
        );
    }

    /**
     * @param string $type
     * @return ExtensionGeneratorInterface|null
     */
    private function createGenerator(string $type): ExtensionGeneratorInterface|null
    {
        $factory = new GeneratorFactory;
        $factory->setStyle($this->getOutput());

        return $factory->create($type);
    }

    /**
     * @param Directory $frontboot
     * @param string $extensionId
     * @return Directory
     * @throws ErrorException
     */
    private function createExtensionDirectory(Directory $frontboot, string $extensionId): Directory
    {
        $folder = $extensionId;

        if (Directory::isDirectoryExists($frontboot->getPath() . DIRECTORY_SEPARATOR . $folder)) {
            $folder = $this->generateUniquiDirName($frontboot, $folder);
        }

        return $frontboot->createSubdirectory($folder);
    }

    /**
     * @param Directory $frontboot
     * @param string $extensionId
     * @return string
     */
    private function generateUniquiDirName(Directory $frontboot, string $extensionId): string
    {
        $index = 1;
        $frontbootPath = $frontboot->getPath();

        do {
            $dirname = $extensionId . '_' . $index++;
            $path = $frontbootPath . DIRECTORY_SEPARATOR . $dirname;
        } while (Directory::isDirectoryExists($path));

        return $dirname;
    }
}