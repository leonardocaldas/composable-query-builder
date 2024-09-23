<?php

namespace ComposableQueryBuilder\Utils;

class Normalizer
{
    public static function boolean($value)
    {
        if (in_array($value, ['true', 'false'])) {
            return $value == "true";
        }

        return $value;
    }

    public static function array($value): mixed
    {
        if (empty($value)) {
            return [];
        }

        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }
}