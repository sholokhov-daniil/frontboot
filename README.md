
![logo](doc/logo.png)

# FrontBoot - менеджер расширений для Bitrix
Библиотека для удобной инициализации JavaScript-фреймворков и пользовательских модулей в Bitrix с использованием стандартного механизма [CJSCore](https://dev.1c-bitrix.ru/api_help/js_lib/my_extension/index.php). 
Модуль позволяет централизованно регистрировать и управлять расширениями, упрощая организацию фронтенда.

## Системные требования

- PHP 8.2 >
- bitrix 16.0 >

## Зависимости

Модуль использует следующие пакеты composer:
- symfony/console 7.3

## Установка

1. Перейдите в папку /local/modules/ или /bitrix/modules/
2. Создайте папку **sholokhov.frontboot**
3. Склонируйте содержимое репозитория в созданную папку
4. Через консоль перейдите в корень модуля и выполните команду ``composer install``, для установки всех зависимостей.
5. Зайдите в административную часть bitrix и перейдите в ``marketplace`` -> ``Установленные решения``. Пример ссылки ``https://example.ru/bitrix/admin/partner_modules.php?lang=ru``
6. Найдите модуль с названием ``FrontBoot (sholokhov.frontboot)`` и установите его

Теперь модуль полностью установлен и готов к работе.

## Регистрация расширения

```bash
php ext registration
```

## Создание расширения

```bash
php ext create
```

## Разрегистрация(удаление) расширения

```bash
# Вместо {extension} необходимо указать существующий ID расширения
php ext unregistration {extension}
```

### Список зарегистрированных расширений

```bash
php ext extensions
```

## Инициализация расширения

Расширение можно инициализировать:

- Через PHP:

```php
CJSCore::Init(['extension_id']);
```

- Через JavaScript:

```js
BX.loadExt('extension_id').then(() => {
    // код после загрузки
});
```

## Документация
Более подробная документация: https://sholokhov-daniil.github.io/frontboot.doc/

[![Telegram](https://img.shields.io/badge/sholokhov22-50514F?style=for-the-badge&logo=telegram&logoColor=white)](https://t.me/sholokhov22)
[![Email](https://img.shields.io/badge/sholokhovdaniil%40yandex.ru-50514F?style=for-the-badge&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAMAAABF0y+mAAAAYFBMVEX4YEr4X0n4XEX4VTz4Uzn4WkH5hnj8v7j91tH94N34ZVD+5uP////+8vD/9/b9zsj7uLD+7+393tr//v75cl/4bFn4UDX7ppv5gHH8wrv3TTH8xsD3Rib92dX4WkP4Z1PMr9nAAAAAnklEQVR4AcTPNQLDQBAEwUNhW8z4/1caM2kv9qS1qP4bbax171jJfBQn6TuJuZnJcn55eH0xrR5QlFUM9Q19BY12NmsFdDl0RulewqEFr0P4AB1Cm8BoA2giaCcro3IzMC4yarsCjYxKbzusmYxHBIULjPUFRF5Gt8LqtIhHBA+jdc8ddVbAdKgPPo4Lqqw/s+NTdZ6n8InWr8HpgQQAHnwKoF6Sk9YAAAAASUVORK5CYII=)](mailto:sholokhovdaniil@yandex.ru)
[![VK](https://img.shields.io/badge/daniil.sholokhov-50514F?style=for-the-badge&logo=vk&logoColor=white)](https://vk.ru/daniil.sholokhov)