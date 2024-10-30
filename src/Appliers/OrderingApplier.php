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

            $fieldName = self::getOrderingColumnName($queryQueryParams, $provider);

            $orderBy = !str_contains($fieldName, ".") ? DB::raw("`$fieldName`") : $fieldName;

            return $builder->orderBy($orderBy, $provider->getSortDirection());
        }

        return $builder;
    }

    private static function getOrderingColumnName(QueryBuilderParams $queryQueryParams, OrderingProvider $provider): string
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
}
