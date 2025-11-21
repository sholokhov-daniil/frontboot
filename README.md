# sholokhov.frontboot

## Введение
Библиотека позволяет инициализировать js фреймворки или свои модули.  
Место размещения не регламентируется и выбирается индивидуально для каждого расширения.  
Регистрация всех расширений производится через штатный механизм [CJSCore](https://dev.1c-bitrix.ru/api_help/js_lib/my_extension/index.php)

## Системные требования
- PHP 8.2 >
- bitrix 16.0 >

## Регистрация
```php
use Sholokhov\FrontBoot\ExtensionRegistrar;

$extensions = [
    'extension_id_1' => '/var/www/example.ru/local/js/test1',
    'extension_id_2' => '/var/www/example.ru/local/js/test2/'
];

ExtensionRegistrar::run($extensions);
```

### Формат данных
В __ExtensionRegistrar::run__ мы обязаны передать массив следующего формата:  
{EXTENSION_ID} => {EXTENSION_ROOT_PATH}

- EXTENSION_ID - уникальный идентификатор расширения в рамках __CJSCore__
- EXTENSION_ROOT_PATH - Путь до корневого каталога расширения

### Примечание
Путь до корневого каталога расширения не обязан иметь на конце разделитель каталогов

```
✅ Верно
/var/www/example.ru/local/js/test1

✅ Верно
/var/www/example.ru/local/js/test2/
```

## Создание

### Структура
```
extension/  # Корневая директория расширения
├── lang  # Папка с языковыми файлами (не обязательно)
│   ├── ru  # ID языка в системе bitrix
│   │   └──  options.php  # Языковые фразы в конфигурационном файле
│   ├── en  
│   │   └──  options.php  
└── options.php # Конфигурация расширения (обязательно)
```
#### Языковой файл
Языковой файл представляет [штатную реализацию](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4789).

```php
<?php
$MESS['UNIQUE_KEY'] = "Текст";
```

### Конфигурация
Конфигурационный файл расширения должен размещаться в корне расширения с названием __options.php__.
Каждый конфигурационный файл обязан вернуть объект конфигурации, для возможности его регистрации.

```php
use Sholokhov\FrontBoot\Extension;
use Sholokhov\FrontBoot\Locator\BaseFrameworkLocator;

/**
 * @var string $id Идентификатор расширения
 * @var string $directory Путь до корня расширения
 */

// Вспомогательный класс, для поиска всех js и css файлов
$locator = new BaseFrameworkLocator($directory);

// Объект конфигурации
$extension = new Extension($id);

// Полный путь до всех css файлов расширения
$extension->css = $locator->getCss();
// или
$extension->css = [
    $directory . 'style.css'
];

// Польный путь до всех js файлов расширения
$extension->js = $locator->getJs();
// или
$extension->js = [
    $directory . 'script.js'
];

// Полный путь до языкового файла расширения
$extension->lang = $locator->getLang();
// или
$extension->lang = $directory . "lang/" . LANGUAGE_ID . "/options.php";

// Пропустить инициализацию core.js
$extension->skipCore = true;

// Инициализировать после регистрации
$extension->autoInit = true;

// Ограничение области подключения расширения
$extension->use = CJSCore::USE_PUBLIC;

// Связанные расширения, которые должны инициализироваться до инициализации текущего расширения
$extension->rel = [
    'ui.alerts'
];

// Возвращаем объект конфигурации, для его регистрации
return $extension;
```
#### Минимальная рабочая версия конфигурации
```php
use Sholokhov\FrontBoot\Extension;

/**
 * @var string $id Идентификатор расширения
 * @var string $directory Путь до корня расширения
 */

$extension = new Extension($id);

// Если есть js файлы
$extension->js = ['path'];

// Если есть css afqks
$extension->css = ['path'];

return $extension;
```

## Инициализация

Инициализация через php
```php
CJSCore::Init(['extension_id_1']);
```

Инициализация через js
```js
BX.loadExt('extension_id_1')
    .then(() => {
        // ...
    })
```
