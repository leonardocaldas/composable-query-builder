<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\QueryBuilderParams;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderingApplier implements Applier
{
    public static function apply(Builder $builder, QueryBuilderParams $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getOrderingProvider();

        if ($provider->hasOrderBy()) {
            $builder->reorder();

            $fieldName = self::getColumnName($queryQueryParams, $provider);

            if (! self::applyOrderByNameMapping($builder, $fieldName, $queryQueryParams)) {
                $fieldName = self::normalizeName($fieldName);
                return $builder->orderBy($fieldName, $provider->getSortDirection());
            }
        }

        return $builder;
    }

    private static function getColumnName(QueryBuilderParams $queryQueryParams, OrderingProvider $provider): string
    {
        $column = $provider->getFieldName();

        $filterNameMapping = data_get($queryQueryParams->getFilterNameMapping(), $column);

        if ($filterNameMapping) {
            return $filterNameMapping;
        }

        $filterNameDefaultTable = $queryQueryParams->getFilterNameDefaultTable();

        if ($filterNameDefaultTable && !Str::contains($column, ".")) {
            $column = sprintf("%s.%s", $filterNameDefaultTable, $column);
        }

        return $column;
    }

    private static function applyOrderByNameMapping(Builder $builder, string $fieldName, QueryBuilderParams $queryQueryParams): bool
    {
        $resolver = $queryQueryParams->getOrderByNameMapping();

        if ($resolved = data_get($resolver, $fieldName)) {
            $dir = $queryQueryParams->getOrderingProvider()->getSortDirection();

            if (is_callable($resolved)) {
                $resolved($builder, $dir);
            } else if (gettype($resolved) == "string") {
                $builder->orderByRaw(sprintf("%s %s", $resolved, $dir));
            }

            return true;
        }

        return false;
    }

    private static function normalizeName(string $name): mixed
    {
        return !str_contains($name, ".") ? DB::raw("`$name`") : $name;
    }
}
