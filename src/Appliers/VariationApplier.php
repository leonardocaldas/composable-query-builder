<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\Params;
use ComposableQueryBuilder\Representation\QueryModifier;
use Illuminate\Database\Query\Builder;

class VariationApplier implements Applier
{
    public static function apply(Builder $builder, Params $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getVariationProvider();

        if ($queryQueryParams->hasVariations()) {
            if ($provider->hasVariation()) {
                $variation = $provider->getVariation();

                self::applyWhen($queryQueryParams, $builder, function (QueryModifier $config) use ($variation) {
                    return $config->getName() == $variation;
                });
            } else {
                self::applyWhen($queryQueryParams, $builder, function (QueryModifier $config) {
                    return $config->isDefault();
                });
            }
        }

        return $builder;
    }

    private static function applyWhen(Params $queryParameters, Builder $builder, callable $callable)
    {
        collect($queryParameters->getModifiers())
            ->filter($callable)
            ->each(function (QueryModifier $parameterVariation) use ($builder) {
                call_user_func($parameterVariation->getCallable(), $builder);
            });
    }
}
