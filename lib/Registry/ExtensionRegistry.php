<?php

namespace Sholokhov\FrontBoot\Registry;

use Psr\Container\ContainerInterface;
use Sholokhov\FrontBoot\Config;

/**
 * Хранилище зарегистрированных расширений
 */
final class ExtensionRegistry implements ContainerInterface
{
    /**
     * @var array
     */
    private readonly array $storage;

    private static self $instance;

    public function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self;
    }

    /**
     * @param string $id
     * @return Config|null
     */
    public function get(string $id): ?Config
    {
        return $this->storage[$id] ?? null;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->storage[$id]);
    }

    /**
     * @param string $id
     * @param Config $extension
     * @return $this
     */
    public function set(string $id, Config $extension): self
    {
        $this->storage[$id] = $extension;
        return $this;
    }
}