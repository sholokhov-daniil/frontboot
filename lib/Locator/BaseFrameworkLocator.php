<?php

namespace Sholokhov\FrontBoot\Locator;

use Bitrix\Main\Diag\Debug;
use SplFileInfo;
use Throwable;

/**
 * Реализует механизм поиска скомпилированных ресурсов расширения,
 * таких как js и css файлы.
 *
 * Основное назначение - подготовка путей к необходимым для инициализации расширения ресурсам
 *
 * @package Sholokhov\FrontBoot\Locator
 */
class BaseFrameworkLocator extends AbstractLocator
{
    /**
     * Относительный путь до директории скомпилированного расширения
     * 
     * @var string 
     */
    protected string $distPath = DIRECTORY_SEPARATOR . 'dist';

    /**
     * Передает список всех скомпилированных js файлов
     *
     * @param string $folder Директория размещения скриптов
     * @return array
     */
    public function getJs(string $folder = 'js'): array
    {
        $files = [];

        try {
            $path = $this->getDistSubFolder($folder);
            $files = $this->getFiles(
                $path,
                fn(SplFileInfo $file) => $file->getExtension() === 'js'
            );
        } catch (Throwable) {
        }

        return $files;
    }

    /**
     * Передает список всех скомпилированных css файлов
     *
     * @param string $folder Директория размещения стилей
     * @return array
     */
    public function getCss(string $folder = 'css'): array
    {
        $files = [];

        try {
            $path = $this->getDistSubFolder($folder);
            $files = $this->getFiles(
                $path,
                fn(SplFileInfo $file) => $file->getExtension() === 'css'
            );
        } catch (Throwable) {
        }

        return $files;
    }

    /**
     * Указать путь до директории скомпилированного расширения
     * 
     * @param string $path
     * @return $this
     */
    public function setDistPath(string $path): static
    {
        $this->distPath = $path;
        return $this;
    }

    /**
     * Формирует полный путь до вложенной скомпилированной папки.
     * 
     * @param string $name Название(путь) вложенного каталога
     * @return string
     */
    protected function getDistSubFolder(string $name): string
    {
        return $this->rootDirectory . $this->distPath . DIRECTORY_SEPARATOR . $name;
    }
}