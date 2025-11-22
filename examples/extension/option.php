<?php

use Sholokhov\FrontBoot\Config;
use Bitrix\Main\Localization\Loc;
use Sholokhov\FrontBoot\Locator\BaseFrameworkLocator;

// Инициализируем языковые файлы
Loc::loadMessages(__FILE__);

// Вспомогательный класс, для поиска всех js и css файлов
$locator = new BaseFrameworkLocator(__DIR__);

// Объект конфигурации
$extension = new Config;

// Полный путь до всех css файлов расширения
$extension->css = $locator->getCss();

// Польный путь до всех js файлов расширения
$extension->js = $locator->getJs();

// Полный путь до языкового файла расширения
$extension->lang = $locator->getLang();

// Пропустить инициализацию core.js
$extension->skipCore = false;

// Инициализировать после регистрации
$extension->autoload = false;

// Связанные расширения, которые должны инициализироваться до инициализации текущего расширения
$extension->rel = [];

// Возвращаем объект конфигурации, для его регистрации
return $extension;