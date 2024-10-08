<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\QueryBuilderParams;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OrderingApplier implements Applier
{
    public static function apply(Builder $builder, QueryBuilderParams $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getOrderingProvider();

        if ($provider->hasOrderBy()) {
            self::clearOrderBy($builder);

            $fieldName = data_get($queryQueryParams->getFilterNameMapping(), $provider->getFieldName(), $provider->getFieldName());

            $orderBy = !str_contains($fieldName, ".") ? DB::raw("`$fieldName`") : $fieldName;

            return $builder->orderBy($orderBy, $provider->getSortDirection());
        }

        return $builder;
    }

    private static function clearOrderBy(Builder $builder)
    {
        $builder->orders = null;

        $builder->bindings['order'] = [];
    }
}
