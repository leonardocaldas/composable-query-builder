<?php

namespace ComposableQueryBuilder;

use ComposableQueryBuilder\Appliers\FilterApplier;
use ComposableQueryBuilder\Appliers\OrderingApplier;
use ComposableQueryBuilder\Appliers\PaginationApplier;
use ComposableQueryBuilder\Appliers\VariationApplier;
use ComposableQueryBuilder\Representation\QueryBuilderResult;

class QueryBuilder
{
    public static function for(QueryBuilderParams $parameters): QueryBuilderResult
    {
        $builder = clone $parameters->getBaseQuery();

        $builder = VariationApplier::apply($builder, $parameters);

        $builder = FilterApplier::apply($builder, $parameters);

        $builder = OrderingApplier::apply($builder, $parameters);

        $builder = PaginationApplier::apply($builder, $parameters);

        return new QueryBuilderResult($parameters, $builder);
    }
}
