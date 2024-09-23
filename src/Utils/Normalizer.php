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
}