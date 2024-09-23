<?php

namespace ComposableQueryBuilder\Filters;

use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use ComposableQueryBuilder\Utils\FilterValueExtractor;
use ComposableQueryBuilder\Utils\FullTextSearch;

class FilterBehaviour
{
    public static function exactMatch(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->where($columnName, $value);
        };
    }

    public static function whereNotNull(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->whereNotNull($columnName, $value);
        };
    }

    public static function whereNull(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->whereNull($columnName, $value);
        };
    }

    public static function notEquals(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $value = str_replace(QueryStringStatementTokens::NOT_EQUALS, '', $value);

            $queryBuilder->where($columnName, '!=', $value);
        };
    }

    public static function betweenDateTime(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $value = FilterValueExtractor::dateTime($value);

            return $queryBuilder->whereBetween($columnName, $value);
        };
    }

    public static function contains(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->where($columnName, 'like', '%' . $value . '%');
        };
    }

    public static function fulltext(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->whereRaw(
                FullTextSearch::normalizeFullTextStatement($columnName, $value)
            );
        };
    }

    public static function startsWith(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->where($columnName, 'like', $value . '%');
        };
    }

    public static function endsWith(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->where($columnName, 'like', '%' . $value);
        };
    }

    public static function whereIn(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            $queryBuilder->whereIn($columnName, $value);
        };
    }

    public static function numericRange(): callable
    {
        return function ($columnName, $value, $queryBuilder) {
            [$symbolClause, $value] = FilterValueExtractor::range($value);

            return $queryBuilder->where($columnName, $symbolClause, $value);
        };
    }
}