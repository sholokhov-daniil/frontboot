<?php

namespace Sholokhov\FrontBoot\Locator;

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
     * @return array
     */
    public function getJs(): array
    {
        $path = $this->getDistSubFolder('js');
        return $this->getFiles($path);
    }

    /**
     * Передает список всех скомпилированных css файлов
     *
     * @return array
     */
    public function getCss(): array
    {
        $path = $this->getDistSubFolder('css');
        return $this->getFiles($path);
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