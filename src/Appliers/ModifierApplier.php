<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\QueryBuilderParams;
use ComposableQueryBuilder\Representation\QueryModifier;
use Illuminate\Database\Query\Builder;

class ModifierApplier implements Applier
{
    public static function apply(Builder $builder, QueryBuilderParams $queryQueryParams): Builder
    {
        $provider = $queryQueryParams->getModifierProvider();

        if ($queryQueryParams->hasModifier()) {
            if ($provider->hasModifier()) {
                $modifier = $provider->getModifier();

                self::applyWhen($queryQueryParams, $builder, function (QueryModifier $config) use ($modifier) {
                    return $config->getName() == $modifier;
                });
            } else {
                self::applyWhen($queryQueryParams, $builder, function (QueryModifier $config) {
                    return $config->isDefault();
                });
            }
        }

        return $builder;
    }

    private static function applyWhen(QueryBuilderParams $queryParameters, Builder $builder, callable $callable): void
    {
        collect($queryParameters->getModifiers())
            ->filter($callable)
            ->each(function (QueryModifier $queryModifier) use ($builder) {
                call_user_func($queryModifier->getCallable(), $builder);
            });
    }
}
