<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\ComposableQueryBuilderParams;
use ComposableQueryBuilder\Representation\QueryVariation;
use Illuminate\Database\Query\Builder;

class VariationApplier implements Applier
{
    public static function apply(Builder $builder, ComposableQueryBuilderParams $queryQueryParameters): Builder
    {
        $provider = $queryQueryParameters->getVariationProvider();

        if ($queryQueryParameters->hasVariations()) {
            if ($provider->hasVariation()) {
                $variation = $provider->getVariation();

                self::applyWhen($queryQueryParameters, $builder, function (QueryVariation $config) use ($variation) {
                    return $config->getName() == $variation;
                });
            } else {
                self::applyWhen($queryQueryParameters, $builder, function (QueryVariation $config) {
                    return $config->isDefault();
                });
            }
        }

        return $builder;
    }

    private static function applyWhen(ComposableQueryBuilderParams $queryParameters, Builder $builder, callable $callable)
    {
        collect($queryParameters->getVariations())
            ->filter($callable)
            ->each(function (QueryVariation $parameterVariation) use ($builder) {
                call_user_func($parameterVariation->getCallable(), $builder);
            });
    }
}
