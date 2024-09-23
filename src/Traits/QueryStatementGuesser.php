<?php

namespace ComposableQueryBuilder\Traits;

use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use Illuminate\Support\Str;

trait QueryStatementGuesser
{
    private function isNotEquals($value): bool
    {
        return is_string($value) && Str::startsWith($value, QueryStringStatementTokens::NOT_EQUALS);
    }

    private function isNull($value): bool
    {
        return $value === QueryStringStatementTokens::NULL;
    }

    private function isNotNull($value): bool
    {
        return $value === QueryStringStatementTokens::NOT_NULL;
    }

    private function isLikeClause($value): bool
    {
        return is_string($value) && !is_numeric($value);
    }

    private function isRangeClause($value): bool
    {
        return is_string($value) && Str::startsWith($value, [
            QueryStringStatementTokens::GREATER_THAN,
            QueryStringStatementTokens::GREATER_THAN_OR_EQUALS,
            QueryStringStatementTokens::LESS_THAN,
            QueryStringStatementTokens::LESS_THAN_OR_EQUALS,
        ]);
    }

    private function isInClause($value): bool
    {
        return is_array($value);
    }
}
