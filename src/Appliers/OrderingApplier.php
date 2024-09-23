<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\ComposableQueryBuilderParams;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OrderingApplier implements Applier
{
    public static function apply(Builder $builder, ComposableQueryBuilderParams $queryQueryParameters): Builder
    {
        $provider = $queryQueryParameters->getOrderingProvider();

        if ($provider->hasOrderBy()) {
            self::clearOrderBy($builder);

            $fieldName = data_get($queryQueryParameters->getFilterResolver(), $provider->getFieldName(), $provider->getFieldName());

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
