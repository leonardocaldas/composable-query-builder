<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\ComposableQueryBuilderParams;
use Illuminate\Database\Query\Builder;

interface Applier
{
    public static function apply(Builder $builder, ComposableQueryBuilderParams $queryQueryParams): Builder;
}
