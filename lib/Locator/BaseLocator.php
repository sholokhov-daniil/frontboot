<?php

namespace Sholokhov\FrontBoot\Locator;

class BaseLocator extends AbstractLocator
{
    /**
     * @return array
     */
    public function getJs(): array
    {
        return $this->getFiles($this->rootDirectory . 'js');
    }

    /**
     * @return array
     */
    public function getCss(): array
    {
        return $this->getFiles($this->rootDirectory . 'css');
    }
}