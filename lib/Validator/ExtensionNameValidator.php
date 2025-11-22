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
        return preg_match('/(?=.*\s)(?=.*[\p{Cyrillic}])(?=.*[^a-zA-Z0-9\s])/u', $name);
    }
}