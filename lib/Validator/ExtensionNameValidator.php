<?php

namespace Sholokhov\FrontBoot\Validator;

/**
 * Проверяет корректность идентификатора расширения
 */
class ExtensionNameValidator
{
    /**
     * @param string $name
     * @return bool
     */
    public static function validate(string $name): bool
    {
        return preg_match('/^(?!frontboot\.)[A-Za-z0-9._-]+$/u', $name);
    }
}