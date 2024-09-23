<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\QueryBuilderParams;
use Illuminate\Database\Query\Builder;

class PaginationApplier implements Applier
{
    public static function apply(Builder $builder, QueryBuilderParams $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getPaginationProvider();

        if (!$provider->shouldPaginate()) {
            return $builder;
        }

        return $builder->forPage($provider->getPage(), $provider->getRowsPerPage());
    }
}
