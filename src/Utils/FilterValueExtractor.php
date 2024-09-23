<?php

namespace ComposableQueryBuilder\Utils;

use Carbon\Carbon;
use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use Illuminate\Support\Str;

class FilterValueExtractor
{
    public static function range($value): array
    {
        $symbols = [
            QueryStringStatementTokens::GREATER_THAN,
            QueryStringStatementTokens::GREATER_THAN_OR_EQUALS,
            QueryStringStatementTokens::LESS_THAN_OR_EQUALS,
            QueryStringStatementTokens::LESS_THAN,
        ];

        foreach ($symbols as $symbol) {
            if (Str::startsWith($value, $symbol)) {
                return [$symbol, Str::replaceFirst($symbol, '', $value)];
            }
        }

        return ["=", $value];
    }

    public static function dateTime(array $values): array
    {
        if (str_contains($values['0'], 'T')) {
            return [
                Carbon::parse($values[0])->utc()->toDateTimeString(),
                Carbon::parse($values[1])->utc()->toDateTimeString(),
            ];
        }

        return ["{$values[0]} 00:00:00", "{$values[1]} 23:59:59"];
    }
}