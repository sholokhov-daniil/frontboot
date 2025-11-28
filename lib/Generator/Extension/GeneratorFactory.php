<?php

namespace Sholokhov\FrontBoot\Generator\Extension;

use Sholokhov\FrontBoot\App;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Создает генератор расширения
 */
class GeneratorFactory
{
    private SymfonyStyle|null $style = null;

    /**
     * Создание генератора
     *
     * @param string $name
     * @return ExtensionGeneratorInterface|null
     */
    public function create(string $name): ExtensionGeneratorInterface|null
    {
        $iterator = App::config()->get('extension-generators') ?: [];
        return isset($iterator[$name]) ? new $iterator[$name]($this->style) : null;
    }

    /**
     * @param SymfonyStyle $style
     * @return $this
     */
    public function setStyle(SymfonyStyle $style): static
    {
        $this->style = $style;
        return $this;
    }
}