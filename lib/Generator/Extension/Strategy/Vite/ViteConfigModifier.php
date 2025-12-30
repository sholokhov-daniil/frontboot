<?php

namespace Sholokhov\Frontboot\Generator\Extension\Strategy\Vite;

use RuntimeException;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\FileNotFoundException;

/**
 * Модифицирует конфигурацию vite
 *
 * Предоставляет удобный fluent interface для добавления импортов,
 * обёртывания defineConfig и добавления различных конфиг-секций.
 */
class ViteConfigModifier
{
    /**
     * Файл конфигурации
     *
     * @var File
     */
    private File $config;

    /**
     * @param Directory $directory Директория размещения vite приложения
     */
    public function __construct(Directory $directory)
    {
        $this->config = $this->findConfigFile($directory);
    }

    /**
     * Модифицирует стандартную конфигурацию vie
     *
     * @return self
     * @throws FileNotFoundException
     * @author Daniil S.
     */
    public function modify(): self
    {
        $this->createBackup();

        $content = $this->getContent();

        $content = $this->addImport($content);
        $content = $this->replaceDefineConfig($content);

        $this->saveContent($content);

        return $this;
    }

    /**
     * Создание резервной копии
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function createBackup(): void
    {
        $fileName = $this->config->getName() . '.backup';
        $path = $this->config->getDirectoryName() . DIRECTORY_SEPARATOR . $fileName;

        $backup = new File($path);
        $backup->putContents($this->config->getContents());
    }

    /**
     * Находит и возвращает файл конфигурации
     *
     * @param Directory $directory
     * @return File
     */
    private function findConfigFile(Directory $directory): File
    {
        $basePath = $directory->getPhysicalPath() . DIRECTORY_SEPARATOR;

        foreach (['vite.config.ts', 'vite.config.js'] as $filename) {
            if (file_exists($basePath . $filename)) {
                return new File($basePath . $filename);
            }
        }

        throw new RuntimeException(
            "vite.config.js or vite.config.ts not found in {$directory->getPhysicalPath()}"
        );
    }

    /**
     * Производит замену метода defineConfig
     *
     * @param string $content
     * @return string
     */
    private function replaceDefineConfig(string $content): string
    {
        if (preg_match('/(export\s+default\s+)defineConfig(\s*\()/m', $content)) {
            return preg_replace(
                '/(export\s+default\s+)defineConfig(\s*\()/m',
                '$1modifyViteConfig$2',
                $content,
                1
            );
        }

        return $content;
    }

    /**
     * Добавляет импорт системной конфигурации модуля
     *
     * @param string $content
     * @return string
     */
    private function addImport(string $content): string
    {
        $import = sprintf(
            'import { modifyViteConfig } from "./frontboot-vite.config.%s";%s',
            $this->config->getExtension(),
            PHP_EOL
        );

        return $import . $content;
    }

    /**
     * Возвращает содержимое конфигурации
     *
     * @return string
     * @throws FileNotFoundException
     */
    private function getContent(): string
    {
        return $this->config->getContents();
    }

    /**
     * Сохраняет содержимое конфигурации
     *
     * @param string $content
     * @return void
     */
    private function saveContent(string $content): void
    {
        $this->config->putContents($content);
    }
}
