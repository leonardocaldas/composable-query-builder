<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\ComposableQueryBuilderParams;
use Illuminate\Database\Query\Builder;

class PaginationApplier implements Applier
{
    public static function apply(Builder $builder, ComposableQueryBuilderParams $queryQueryParameters): Builder
    {
        $provider = $queryQueryParameters->getPaginationProvider();

        if (!$provider->shouldPaginate()) {
            return $builder;
        }

        return $builder->forPage($provider->getPage(), $provider->getRowsPerPage());
    }
}
