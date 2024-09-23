<?php

namespace ComposableQueryBuilder\Traits;

use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use Carbon\Carbon;

trait QueryStatementFieldNormalizer
{
    use FullTextSearch;

    protected function normalizeNotEqualsStatement(string $value)
    {
        return str_replace(QueryStringStatementTokens::NOT_EQUALS, '', $value);
    }

    protected function normalizeLikeStatement($value)
    {
        return QueryStringStatementTokens::LIKE . $value . QueryStringStatementTokens::LIKE;
    }

    protected function normalizeFullTextStatement(string $typeResolver, string $value)
    {
        $typeResolver    = str_replace('fulltext:', '', $typeResolver);
        $fullTextColumns = explode(',', trim($typeResolver));

        return $this->getMatchAgainstStatement($fullTextColumns, $value);
    }

    protected function extractRangeClauseStatement($value): array
    {
        $symbols = [
            QueryStringStatementTokens::GREATER_THAN,
            QueryStringStatementTokens::GREATER_THAN_OR_EQUALS,
            QueryStringStatementTokens::LESS_THAN_OR_EQUALS,
            QueryStringStatementTokens::LESS_THAN,
        ];

        foreach ($symbols as $symbol) {
            if (starts_with($value, $symbol)) {
                return [$symbol, str_replace_first($symbol, '', $value)];
            }
        }

        return ["=", $value];
    }

    protected function normalizeDateTimeStatement(array $values): array
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
