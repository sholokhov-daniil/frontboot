<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy;

use CopyDirFiles;

use Sholokhov\FrontBoot\App;
use Sholokhov\FrontBoot\Generator\Extension\ExtensionGeneratorInterface;

use Bitrix\Main\Error;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Result;

/**
 * Генерирует простое пустое расширение
 */
class DefaultStrategy implements ExtensionGeneratorInterface
{
    public function __construct()
    {
    }

    public function generate(Directory $directory): Result
    {
        $result = new Result;

        if (!$this->copy($directory)) {
            return $result->addError(new Error('Error generate'));
        }

        return $result;
    }

    /**
     * Копирование файлов расширения
     *
     * @param Directory $directory
     * @return bool
     */
    private function copy(Directory $directory): bool
    {
        $appDir = App::getRootDir();
        return CopyDirFiles(
            $appDir->getPhysicalPath() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'extension',
            $directory->getPhysicalPath(),
            true,
            true
        );
    }
}