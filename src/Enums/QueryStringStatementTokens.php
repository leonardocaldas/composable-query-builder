<?php

namespace ComposableQueryBuilder\Enums;

class QueryStringStatementTokens
{
    public const NOT_EQUALS             = "<>";
    public const GREATER_THAN           = ">";
    public const GREATER_THAN_OR_EQUALS = ">=";
    public const LESS_THAN              = "<";
    public const LESS_THAN_OR_EQUALS    = "<=";
    public const NOT_NULL               = "IS_NOT_NULL";
    public const NULL                   = "IS_NULL";
    public const LIKE                   = "%";
}
