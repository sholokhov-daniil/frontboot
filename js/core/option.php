<?php

use Sholokhov\FrontBoot\Config;
use Sholokhov\FrontBoot\Locator\BaseLocator;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$locator = new BaseLocator(__DIR__);

$extension = new Config;
$extension->js = $locator->getJs('dist');
$extension->skipCore = false;
$extension->autoload = true;

return $extension;