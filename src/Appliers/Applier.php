<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\Params;
use Illuminate\Database\Query\Builder;

interface Applier
{
    public static function apply(Builder $builder, Params $queryQueryParams): Builder;
}
