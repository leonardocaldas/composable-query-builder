<?php

namespace ComposableQueryBuilder;

use ComposableQueryBuilder\Appliers\FilterApplier;
use ComposableQueryBuilder\Appliers\OrderingApplier;
use ComposableQueryBuilder\Appliers\PaginationApplier;
use ComposableQueryBuilder\Appliers\VariationApplier;
use ComposableQueryBuilder\Representation\ComposableQueryBuilderResult;

class ComposableQueryBuilderRunner
{
    public static function runWith(ComposableQueryBuilderParams $parameters): ComposableQueryBuilderResult
    {
        $builder = clone $parameters->getBaseQuery();

        $builder = VariationApplier::apply($builder, $parameters);

        $builder = FilterApplier::apply($builder, $parameters);

        $builder = OrderingApplier::apply($builder, $parameters);

        $builder = PaginationApplier::apply($builder, $parameters);

        return new ComposableQueryBuilderResult($parameters, $builder);
    }

    public static function first(ComposableQueryBuilderParams $parameters)
    {
        $result = self::runWith($parameters);

        return $result->fetchFirst();
    }
}
