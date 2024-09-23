<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\Params;
use Illuminate\Database\Query\Builder;

class PaginationApplier implements Applier
{
    public static function apply(Builder $builder, Params $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getPaginationProvider();

        if (!$provider->shouldPaginate()) {
            return $builder;
        }

        return $builder->forPage($provider->getPage(), $provider->getRowsPerPage());
    }
}
