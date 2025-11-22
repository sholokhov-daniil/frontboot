# sholokhov.frontboot

## Введение
Библиотека позволяет инициализировать js фреймворки или свои модули.  
Место размещения не регламентируется и выбирается индивидуально для каждого расширения.  
Регистрация всех расширений производится через штатный механизм [CJSCore](https://dev.1c-bitrix.ru/api_help/js_lib/my_extension/index.php)

## Системные требования
- PHP 8.2 >
- bitrix 16.0 >

## Регистрация

Регистрация расширения производится через cli (консоль).  
Необходимо перейти в корень модуля и вызвать команду регистрации.

```bash
php ext reg -i "extension_id_1" -p "/var/www/example.ru/local/js/test1"

# или
php ext registration --id="extension_id_1" --path="/var/www/example.ru/local/js/test1"
```
Доступны следующие опции команды:
- **--id** - Идентификатор js расширения в рамках **CJSCore**. 
- **-i** - Алиас команды **--id**
- **--path** - Путь до корня расширения
- **-p** - Алиас команды **--path** 


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
└── option.php # Конфигурация расширения (обязательно)
```
#### Языковой файл
Языковой файл представляет [штатную реализацию](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4789).

```php
<?php
$MESS['UNIQUE_KEY'] = "Текст";
```

### Конфигурация
Конфигурационный файл расширения должен размещаться в корне расширения с названием __option.php__.
Каждый конфигурационный файл обязан вернуть объект конфигурации, для возможности его регистрации.

```php
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
// или
$extension->css = [
    __DIR__ . '/style.css'
];

// Польный путь до всех js файлов расширения
$extension->js = $locator->getJs();
// или
$extension->js = [
    __DIR__ . '/script.js'
];

// Полный путь до языкового файла расширения
$extension->lang = $locator->getLang();
// или
$extension->lang = $directory . "lang/" . LANGUAGE_ID . "/options.php";

// Пропустить инициализацию core.js
$extension->skipCore = true;

// Инициализировать после регистрации
$extension->autoload = true;

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
use Sholokhov\FrontBoot\Config;

$extension = new Config;

// Если есть js файлы
$extension->js = ['path'];

// Если есть css файлы
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
