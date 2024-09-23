<?php

namespace ComposableQueryBuilder\Enums;

enum QueryFilterType: string
{
    public const BETWEEN_DATETIME = "BETWEEN_DATETIME";
    public const BOOLEAN_NOT_NULL = "BOOLEAN_NOT_NULL";
    public const WHERE_IN         = "WHERE_IN";
    public const DECIMAL_BOOLEAN  = "DECIMAL_BOOLEAN";
    public const EXACT_MATCH      = "EXACT_MATCH";
}
