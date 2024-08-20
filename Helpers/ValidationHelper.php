<?php

namespace Helpers;

use Types\ValueType;

class ValidationHelper {
    public static function validateFields(array $fields, array $data): array {
        $validatedData = [];

        foreach ($fields as $field => $type) {
            if (!isset($data[$field]) || ($data)[$field] === '') {
                throw new \InvalidArgumentException("Missing field: $field");
            }

            $value = $data[$field];

            $validatedValue = match ($type) {
                ValueType::STRING => is_string($value) ? $value : throw new \InvalidArgumentException("The provided value is not a valid string."),
                ValueType::INT => self::integer($value),
                ValueType::FLOAT => filter_var($value, FILTER_VALIDATE_FLOAT),
                ValueType::DATE => self::validateDate($value),
                ValueType::EMAIL => filter_var($value, FILTER_VALIDATE_EMAIL),
                ValueType::PASSWORD =>
                    is_string($value) &&
                    strlen($value) >= 8 && // Minimum 8 characters
                    preg_match('/[A-Z]/', $value) && // 少なくとも1文字の大文字
                    preg_match('/[a-z]/', $value) && // 少なくとも1文字の小文字
                    preg_match('/\d/', $value) && // 少なくとも1桁
                    preg_match('/[\W_]/', $value) // 少なくとも1つの特殊文字（アルファベット以外の文字）
                        ? $value : throw new \InvalidArgumentException("The provided value is not a valid password."),
                default => throw new \InvalidArgumentException(sprintf("Invalid type for field: %s, with type %s", $field, $type)),
            };

            if ($validatedValue === false) {
                throw new \InvalidArgumentException(sprintf("Invalid value for field: %s", $field));
            }

            $validatedData[$field] = $validatedValue;
        }

        return $validatedData;
    }
}
