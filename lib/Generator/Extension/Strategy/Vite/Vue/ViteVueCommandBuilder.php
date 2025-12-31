<?php

namespace Sholokhov\FrontBoot\Generator\Extension\Strategy\Vite\Vue;

use Bitrix\Main\IO\Directory;

class ViteVueCommandBuilder
{
    private array $features = [];

    /**
     * Версия vite vue
     * 
     * @var string
     */

    private string $version = 'latest';

    /**
     * Наименование проекта
     * 
     * @var string
     */
    private string $projectName = 'example';

    private Directory $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Создать консольную команду
     * 
     * @return string
     */
    public function create(): string
    {
        return sprintf(
            "cd %s && npm create vue@%s %s %s",
            escapeshellarg(dirname($this->directory->getPhysicalPath())),
            $this->version,
            $this->projectName,
            $this->getFeatureCommand()
        );
    }

    /**
     * Указываем наименование генерируемого проекта
     * 
     * @param string $name
     * @return ViteVueCommandBuilder
     */
    public function setProjectName(string $name): self
    {
        $this->projectName = $name;
        return $this;
    }

    /**
     * Указываем желаемую версию vite vue
     * 
     * @param string $version
     * @return ViteVueCommandBuilder
     */
    public function setVersion(string $version = 'latest'): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Указать доступные расщирения
     *
     * @param Feature[] $features
     * @return self
     */
    public function setFeatures(array $features): self
    {
        $this->features = [];

        foreach($features as $feature) {
            $this->addFeature($feature);
        }

        return $this;
    }

    /**
     * Добавить расширение
     * 
     * @param Feature $feature
     * @return void
     */
    public function addFeature(Feature $feature): void
    {
        if (isset($this->features[$feature->value]) === false) {
            $this->features[$feature->value] = $feature;
        }
    }

    /**
     * Удалить расширение
     * 
     * @param Feature $feature
     * @return void
     */
    public function removeFeature(Feature $feature): void
    {
        unset($this->features[$feature->value]);
    }

    /**
     * Формирует список расширений
     *
     * @return string
     */
    private function getFeatureCommand(): string
    {
        $line =  implode(
            ' ',
            array_map(
                fn(Feature $feature) => "--$feature->value",
                $this->features
            )
        );

        if ($line) {
            $line = '-- ' . $line;
        }

        return $line;
    }
}
