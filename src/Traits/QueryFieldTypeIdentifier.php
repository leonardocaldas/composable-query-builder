<?php

namespace ComposableQueryBuilder\Traits;

use ComposableQueryBuilder\Enums\QueryFilterType;

trait QueryFieldTypeIdentifier
{
    protected function isWhereInType($resolver, $field)
    {
        return !empty($resolver[$field]) && $resolver[$field] == QueryFilterType::WHERE_IN;
    }

    protected function isBetweenDateTimeType($resolver, $field)
    {
        return !empty($resolver[$field]) && $resolver[$field] == QueryFilterType::BETWEEN_DATETIME;
    }

    protected function isDecimalBooleanType($resolver, $field)
    {
        return !empty($resolver[$field]) && $resolver[$field] == QueryFilterType::DECIMAL_BOOLEAN;
    }

    protected function isBooleanNotNull($resolver, $field)
    {
        return !empty($resolver[$field]) && $resolver[$field] == QueryFilterType::BOOLEAN_NOT_NULL;
    }

    protected function isExactMatchType($resolver, $field)
    {
        return !empty($resolver[$field]) && $resolver[$field] == QueryFilterType::EXACT_MATCH;
    }

    protected function isCustomType($resolver, $field)
    {
        return !empty($resolver[$field]) && is_callable($resolver[$field]);
    }

    protected function isFullTextType($resolver, $field)
    {
        return !empty($resolver[$field]) && starts_with($resolver[$field], 'fulltext');
    }
}
