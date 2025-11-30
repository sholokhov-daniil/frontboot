<?php

namespace Sholokhov\FrontBoot\Locator;

use SplFileInfo;

class BaseLocator extends AbstractLocator
{
    /**
     * @param string $folder
     * @return array
     */
    public function getJs(string $folder = 'js'): array
    {
        return $this->getFiles(
            $this->rootDirectory . $folder,
            fn(SplFileInfo $file) => $file->getExtension() === 'js'
        );
    }

    /**
     * @param string $folder
     * @return array
     */
    public function getCss(string $folder = 'css'): array
    {
        return $this->getFiles(
            $this->rootDirectory . $folder,
            fn(SplFileInfo $file) => $file->getExtension() === 'css'
        );
    }
}