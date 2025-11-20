<?php

namespace Sholokhov\FrontBoot;

use CJSCore;

/**
 * Описывает js расширение
 *
 * @package Sholokhov\FrontBoot
 */
class Extension
{
    /**
     * Уникальный идентификатор расширения
     *
     * @var string
     */
    public readonly string $id;

    /**
     * Директория хранения расширения
     *
     * @var string
     */
    public readonly string $directory;

    /**
     * Полный путь до js файлов расширения
     *
     * @var array
     */
    public array $js = [];

    /**
     * Полный путь до css файлов расширения
     *
     * @var array
     */
    public array $css = [];

    /**
     * Список зависимостей.
     * При подключении собственного расширения зависимости будут подключены автоматически.
     *
     * @var string
     */
    public string $lang = '';

    /**
     * При подключении расширения не требуется подключение core.js
     *
     * @var bool
     */
    public bool $skipCore = false;

    /**
     * Произвести автоматическую инициализацию расширения
     *
     * @var bool
     */
    public bool $autoInit = false;

    /**
     * Ограничение области подключения расширения.
     * В качестве значения необходимо указать {@see CJSCore::USE_PUBLIC} или {@see CJSCore::USE_ADMIN}
     *
     * @var string|null
     */
    public string|null $use = null;

    /**
     * @param string $id Уникальный идентификатор расширения
     * @param string $directory Директория расширения
     */
    public function __construct(string $id, string $directory = '')
    {
        $this->id = $id;
        $this->directory = $directory;
    }
}