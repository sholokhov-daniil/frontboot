<?php

namespace Sholokhov\FrontBoot\Generator\Extension;

use Bitrix\Main\Result;
use Bitrix\Main\IO\Directory;

/**
 * Занимается генераций расширения при использовании команды php ext create
 */
interface ExtensionGeneratorInterface
{
    /**
     * Запустить генерацию расширения
     *
     * @param Directory $directory Директория расширения
     * @return Result
     */
    public function generate(Directory $directory): Result;
}