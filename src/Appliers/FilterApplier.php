<?php

namespace ComposableQueryBuilder\Appliers;

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

                $this->addWhereClause($fullColumnName, $value);
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

    private function addWhereClause($column, $value)
    {
        $filterTypeResolver = $this->parameters->getFilterTypeResolver();

        if ($this->isCustomType($filterTypeResolver, $column)) {
            return $filterTypeResolver[$column]($this->builder, $value, $column);
        }

        if ($this->isBetweenDateTimeType($filterTypeResolver, $column)) {
            $value = $this->normalizeDateTimeStatement($value);

            return $this->builder->whereBetween($column, $value);
        }

        if ($this->isDecimalBooleanType($filterTypeResolver, $column)) {
            if ($value) {
                return $this->builder->where($column, '>', 0);
            } else {
                return $this->builder->where($column, 0);
            }
        }

        if ($this->isBooleanNotNull($filterTypeResolver, $column)) {
            return $value
                ? $this->builder->whereNotNull($column)
                : $this->builder->whereNull($column);
        }

        if ($this->isNull($value)) {
            return $this->builder->whereNull($column);
        }

        if ($this->isNotNull($value)) {
            return $this->builder->whereNotNull($column);
        }

        if ($this->isNotEquals($value)) {
            return $this->builder->where($column, '<>', $this->normalizeNotEqualsStatement($value));
        }

        if ($this->isRangeClause($value)) {
            [$symbolClause, $value] = $this->extractRangeClauseStatement($value);

            return $this->builder->where($column, $symbolClause, $value);
        }

        if ($this->isLikeClause($value) && !$this->isExactMatchType($filterTypeResolver, $column)) {
            if ($this->isFullTextType($filterTypeResolver, $column)) {
                return $this->builder->whereRaw(
                    $this->normalizeFullTextStatement($filterTypeResolver[$column], $value)
                );
            }

            return $this->builder->where($column, 'like', $this->normalizeLikeStatement($value));
        }

        if ($this->isInClause($value)) {
            return $this->builder->whereIn($column, $value);
        }

        return $this->builder->where($column, $value);
    }
}
