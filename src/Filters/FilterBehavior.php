<?php

namespace ComposableQueryBuilder\Filters;

use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use ComposableQueryBuilder\Utils\FilterValueExtractor;
use ComposableQueryBuilder\Utils\FullTextSearch;

class FilterBehavior
{
    public static function exactMatch(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->where($columnName, $value);
        };
    }

    public static function whereNullable(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            if (filled($value)) {
                $queryBuilder->whereNotNull($columnName, $value);
            } else if ($value == 0 || $value == "0") {
                $queryBuilder->whereNull($columnName, $value);
            }
        };
    }

    public static function whereNotNull(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->whereNotNull($columnName, $value);
        };
    }

    public static function whereJsonContainsString(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->whereJsonContains($columnName, (string)$value);
        };
    }

    public static function whereJsonContainsInt(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->whereJsonContains($columnName, intval($value));
        };
    }

    public static function whereNull(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->whereNull($columnName, $value);
        };
    }

    public static function notEquals(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $value = str_replace(QueryStringStatementTokens::NOT_EQUALS, '', $value);

            $queryBuilder->where($columnName, '!=', $value);
        };
    }

    public static function betweenDateTime(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $value = FilterValueExtractor::dateTime($value);

            return $queryBuilder->whereBetween($columnName, $value);
        };
    }

    public static function contains(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->where($columnName, 'like', '%' . $value . '%');
        };
    }

    public static function fulltext(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->whereRaw(
                FullTextSearch::normalizeFullTextStatement($columnName, $value)
            );
        };
    }

    public static function startsWith(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->where($columnName, 'like', $value . '%');
        };
    }

    public static function endsWith(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            $queryBuilder->where($columnName, 'like', '%' . $value);
        };
    }

    public static function whereIn(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            if (is_string($value) || is_numeric($value)) {
                $value = [$value];
            }

            $queryBuilder->whereIn($columnName, $value);
        };
    }

    public static function numericRange(): callable
    {
        return function ($queryBuilder, $value, $columnName) {
            [$symbolClause, $value] = FilterValueExtractor::range($value);

            return $queryBuilder->where($columnName, $symbolClause, $value);
        };
    }
}