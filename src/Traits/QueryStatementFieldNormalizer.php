<?php

namespace ComposableQueryBuilder\Traits;

use ComposableQueryBuilder\Enums\QueryStringStatementTokens;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait QueryStatementFieldNormalizer
{
    use FullTextSearch;

    protected function normalizeNotEqualsStatement(string $value): string
    {
        return str_replace(QueryStringStatementTokens::NOT_EQUALS, '', $value);
    }

    protected function normalizeLikeStatement($value): string
    {
        return QueryStringStatementTokens::LIKE . $value . QueryStringStatementTokens::LIKE;
    }

    protected function normalizeFullTextStatement(string $typeResolver, string $value): string
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
            if (Str::startsWith($value, $symbol)) {
                return [$symbol, Str::replaceFirst($symbol, '', $value)];
            }
        }

        return ["=", $value];
    }
}
