<?php

namespace Sholokhov\FrontBoot\Console\Commands;

use CJSCore;
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
        do {
            $id = (string)$this->ask('Extension name');
        } while (!mb_strlen($id));

        $description = $this->ask('Extension description');

        $extensionDir = null;

        try {
            // Создать директорию, для расширения
            if (CJSCore::IsExtRegistered($id)) {
                $this->error('The extension has already been registered');
                return self::FAILURE;
            }

            $directory = $this->getFrontBootDirectory();
            if (!$directory) {
                $this->error('Error creating a directory with extensions');
                return self::FAILURE;
            }

            $extensionDir = $this->createExtensionDirectory($directory, $id);

            $result = CopyDirFiles(
                dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'extension',
                $extensionDir->getPhysicalPath(),
                true,
                true
            );

            if (!$result) {
                $this->error('Error generating the extension');
                $extensionDir->delete();
                return self::FAILURE;
            }

            $result = ExtensionTable::add([
                'ID' => $id,
                'PATH' => $extensionDir->getPath(),
                'DESCRIPTION' => $description,
            ]);

            if (!$result->isSuccess()) {
                array_map(
                    fn(string $message) => $this->error($message),
                    $result->getErrorMessages()
                );

                return self::FAILURE;
            }
        } catch (Throwable $exception) {
            $extensionDir?->delete();
            throw $exception;
        }


        $this->frame([
            " Success!",
            " Extension $id created",
            "",
            " Include extension in php",
            " CJSCore::Init(['$id']);",
            "",
            " Include extension in js",
            " BX.loadExt('$id').then(() => {\n     // The code after loading\n });",
            "",
            " Extension Directory",
            " {$extensionDir->getPath()}"
        ]);

        return self::SUCCESS;
    }

    /**
     * @return Directory|null
     */
    private function getFrontBootDirectory(): Directory|null
    {
        $path = Application::getDocumentRoot() . '/local/frontboot/';
        $directory = new Directory($path);

        if (!$directory->isExists()) {
            $directory->create();

            if (!$directory->isExists()) {
                return null;
            }
        }

        return $directory;
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