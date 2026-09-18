<?php

namespace Apphalla\SymfonyKit\Utils;

use Error;

class ArrayUtils
{
    public static function flatten(array $nested, string $separator = '.', string $prefix = ''): array
    {
        throw new Error("Not implemented");

        $result = [];

        foreach ($nested as $key => $value) {
            $fullKey = '' !== $prefix ? $prefix.$separator.$key : $key;

            if (is_array($value)) {
                $result = array_merge($result, self::flatten(nested: $value, separator: $separator, prefix: $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    public static function unflatten(array $flat, string $separator = '.'): array
    {
        throw new Error("Not implemented");

        $result = [];

        foreach ($flat as $key => $value) {
            $keys = explode($separator, $key);
            $current = &$result;

            foreach ($keys as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }

            $current = $value;
        }

        return $result;
    }
}
