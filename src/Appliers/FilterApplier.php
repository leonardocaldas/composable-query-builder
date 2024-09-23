<?php

namespace ComposableQueryBuilder\Appliers;

use ComposableQueryBuilder\Filters\FilterBehaviour;
use ComposableQueryBuilder\QueryBuilderParams;
use ComposableQueryBuilder\Traits\QueryFieldTypeIdentifier;
use ComposableQueryBuilder\Traits\QueryStatementFieldNormalizer;
use ComposableQueryBuilder\Traits\QueryStatementGuesser;
use ComposableQueryBuilder\Utils\Normalizer;
use Illuminate\Database\Query\Builder;

class FilterApplier implements Applier
{
    use QueryStatementGuesser;
    use QueryFieldTypeIdentifier;
    use QueryStatementFieldNormalizer;

    private function __construct(
        private readonly Builder $builder,
        private readonly QueryBuilderParams $parameters,
    ) {}

    public static function apply(Builder $builder, QueryBuilderParams $queryParams): Builder
    {
        return (new self($builder, $queryParams))->run();
    }

    private function run(): Builder
    {
        $filters = $this->parameters->getFilterProvider()->getFilters();

        $this->applyDefaultFilters($filters);
        $this->applyProvidedFilters($filters);

        return $this->builder;
    }

    private function applyDefaultFilters($filters): void
    {
        $defaultFilters = $this->parameters->getDefaultFilters();

        foreach ($defaultFilters as $name => $closure) {
            if (!isset($filters[$name])) {
                call_user_func($closure, $this->builder, $filters);
            }
        }
    }

    private function applyProvidedFilters($filters): void
    {
        if (!$this->parameters->getFiltersEnabled()) {
            return;
        }

        $allowedFilters    = $this->parameters->getAllowedFilters();
        $notAllowedFilters = $this->parameters->getExcludeFilters();

        foreach ($filters as $column => $value) {
            $fullColumnName = $this->getFilterColumn($column);

            if (!empty($allowedFilters) && !in_array($fullColumnName, $allowedFilters)) {
                continue;
            }

            if (!empty($notAllowedFilters) && in_array($fullColumnName, $notAllowedFilters)) {
                continue;
            }

            if (!$this->shouldNotApplyFilter($value)) {
                $value = Normalizer::boolean($value);

                $behaviour = $this->getBehaviour($fullColumnName, $value);
                $behaviour($fullColumnName, $value, $this->builder);
            }
        }
    }

    private function getFilterColumn($column)
    {
        return data_get($this->parameters->getFilterNameMapping(), $column, $column);
    }

    private function shouldNotApplyFilter($value): bool
    {
        return empty($value) && $value !== 0 && $value !== '0';
    }

    private function getBehaviour($column, $value): callable
    {
        $filterBehaviour = $this->parameters->getFilterBehaviour();

        if (!empty($filterBehaviour[$column]) && is_callable($filterBehaviour[$column])) {
            return $filterBehaviour[$column];
        }

        if ($this->isNull($value)) {
            return FilterBehaviour::whereNull();
        }

        if ($this->isNotNull($value)) {
            return FilterBehaviour::whereNotNull();
        }

        if ($this->isNotEquals($value)) {
            return FilterBehaviour::notEquals();
        }

        if ($this->isRangeClause($value)) {
            return FilterBehaviour::numericRange();
        }

        if ($this->isLikeClause($value)) {
            return FilterBehaviour::contains();
        }

        if ($this->isInClause($value)) {
            return FilterBehaviour::whereIn();
        }

        return FilterBehaviour::exactMatch();
    }
}
