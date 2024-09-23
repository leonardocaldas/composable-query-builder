<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\QueryBuilderParams;
use Illuminate\Database\Query\Builder;

interface Applier
{
    public static function apply(Builder $builder, QueryBuilderParams $queryQueryParams): Builder;
}
