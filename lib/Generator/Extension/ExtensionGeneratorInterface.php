<?php

namespace Sholokhov\FrontBoot\Generator\Extension;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\Result;

/**
 * Занимается генераций расширения при использовании команды php ext create
 */
interface ExtensionGeneratorInterface
{
    /**
     * Запустить генерацию расширения
     *
     * @param Directory $directory Директория размещения расширения
     * @param string $name Наименование расширения
     * @return Result
     */
    public function generate(Directory $directory, string $name): Result;
}