<?php

namespace Sholokhov\FrontBoot\Locator;

use SplFileInfo;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Bitrix\Main\Application;

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
        $this->rootDirectory = rtrim($rootDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
        $lang = LANGUAGE_ID ?: 'ru';
        $directory = $this->rootDirectory . 'lang' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'option.php';

        return $this->modifyPath($directory);
    }

    /**
     * Путь до конфигурационного php файла
     *
     * @param string $directory
     * @return string
     */
    public static function getOption(string $directory): string
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return $directory . 'option.php';
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

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($callback && !$callback($file)) {
                continue;
            }


            $result[] = $this->modifyPath($file->getRealPath());
        }

        return $result;
    }

    /**
     * Удаление из пути document root
     *
     * @param string $path
     * @return string
     */
    protected function modifyPath(string $path): string
    {
        return str_replace(Application::getDocumentRoot(), '', $path);
    }
}