<?php

namespace Sholokhov\FrontBoot\Locator;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Производит поиск всех необходимых файлов, для инициализации расширения
 *
 * @package Sholokhov\FrontBoot\Locator
 */
abstract class AbstractLocator
{
    /**
     * Корневой каталог расширения
     *
     * @var string
     */
    protected readonly string $rootDirectory;

    /**
     * @param string $rootDirectory Корневой каталог расширения
     */
    public function __construct(string $rootDirectory)
    {
    }

    /**
     * Производит поиск всех js файлов расширения требующих инициализацию
     *
     * @return array
     */
    abstract public function getJs(): array;

    /**
     * Производит поиск всех css файлов расширения требующих инициализацию
     *
     * @return array
     */
    abstract public function getCss(): array;

    /**
     * Путь до хранения языковых файлов
     *
     * @return string
     */
    public function getLang(): string
    {
        return $this->getOption();
    }

    /**
     * Путь до конфигурационного php файла
     *
     * @return string
     */
    public function getOption(): string
    {
        return $this->rootDirectory . DIRECTORY_SEPARATOR . 'options.php';
    }

    /**
     * Производит рекурсивный обход каталога и возвращает найденные файлы
     *
     * Если проверяемый файл не проходит проверку, то он не включается в результирующий массив.
     *
     * @param string $directory Стартовая директория
     * @param callable|null $callback Выполняет валидацию файла - возвращает логическое значение.
     * @return array
     */
    public function getFiles(string $directory, callable $callback = null): array
    {
        $result = [];
        $iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($callback && !$callback($file)) {
                continue;
            }

            $result[] = $file->getRealPath();
        }

        return $result;
    }
}