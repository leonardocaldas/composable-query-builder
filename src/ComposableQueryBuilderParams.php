<?php

namespace ComposableQueryBuilder;

use Closure;
use ComposableQueryBuilder\Providers\Contracts\FilterProvider;
use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\Providers\Contracts\PaginationProvider;
use ComposableQueryBuilder\Providers\Contracts\VariationProvider;
use ComposableQueryBuilder\Providers\RequestProvider;
use ComposableQueryBuilder\Representation\QueryModifier;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class ComposableQueryBuilderParams
{
    private Builder $baseQuery;

    private array $filterNameMapping = [];

    private array $filterTypeResolver = [];

    /**
     * @var QueryModifier[] $modifiers
     */
    private $modifiers = [];

    /**
     * @var Map<String, Callable<Builder>> $defaultFilters
     */
    private $defaultFilters = [];

    /**
     * @var callable $aggregation
     */
    private $aggregation;

    private ?Closure $resultMapper           = null;
    private ?Closure $resultAllEntriesMapper = null;
    private ?Closure $resultOrderBy          = null;

    private FilterProvider $filterProvider;

    private OrderingProvider $orderingProvider;

    private PaginationProvider $paginationProvider;

    private VariationProvider $variationProvider;

    private bool  $filtersEnabled    = true;
    private array $allowedFilters = [];
    private array $excludeFilters = ['token'];

    private bool $fetchOnlyFirst = false;
    private bool $shouldLogQuery = false;

    private function __construct(?RequestProvider $requestProvider = null)
    {
        $defaultProvider = $requestProvider ?? resolve(RequestProvider::class);

        $this->paginationProvider
            = $this->variationProvider
            = $this->filterProvider
            = $this->orderingProvider
            = $defaultProvider;
    }

    public static function new(): self
    {
        return new ComposableQueryBuilderParams();
    }

    public static function newFromRequest(Request $request): self
    {
        return new ComposableQueryBuilderParams(
            new RequestProvider($request)
        );
    }

    public function for($builder): self
    {
        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $builder = $builder->toBase();
        }

        $this->baseQuery = $builder;
        return $this;
    }

    public function setPaginationProvider(PaginationProvider $paginationProvider): self
    {
        $this->paginationProvider = $paginationProvider;
        return $this;
    }

    public function setFilterProvider(FilterProvider $filterProvider): self
    {
        $this->filterProvider = $filterProvider;
        return $this;
    }

    public function setModifierProvider(VariationProvider $variationProvider): self
    {
        $this->variationProvider = $variationProvider;
        return $this;
    }

    public function setOrderingProvider(OrderingProvider $orderingProvider): self
    {
        $this->orderingProvider = $orderingProvider;
        return $this;
    }

    public function filterNameMapping(array $mapping): self
    {
        $this->filterNameMapping = $mapping;
        return $this;
    }

    public function logQuery(): self
    {
        $this->shouldLogQuery = true;
        return $this;
    }

    public function filterBehaviour(array $typeResolver): self
    {
        $this->filterTypeResolver = $typeResolver;
        return $this;
    }

    public function excludeFilters(array $filters): self
    {
        $this->excludeFilters = array_merge($this->excludeFilters, $filters);
        return $this;
    }

    public function disableFilters(): self
    {
        $this->filtersEnabled = false;
        return $this;
    }

    public function onlyAllowFiltersInNameMapping(): self
    {
        return $this->setAllowedFilters(
            array_values($this->filterNameMapping)
        );
    }

    public function setAllowedFilters(array $filters): self
    {
        $this->allowedFilters = array_merge($this->allowedFilters, $filters);
        return $this;
    }

    public function addQueryModifier($name, callable $callable, $default = false): self
    {
        $this->modifiers[] = new QueryModifier($name, $callable, $default);
        return $this;
    }

    public function withAggregation(callable $callable): self
    {
        $this->aggregation = $callable;
        return $this;
    }

    public function defaultFilter($filterName, callable $callable): self
    {
        $this->defaultFilters[$filterName] = $callable;
        return $this;
    }

    public function withResultMapper(Closure $mapper): self
    {
        $this->resultMapper = $mapper;
        return $this;
    }

    public function withResultOrderBy(Closure $closure): self
    {
        $this->resultOrderBy = $closure;
        return $this;
    }

    public function withResultAllEntriesMapper(Closure $mapper): self
    {
        $this->resultAllEntriesMapper = $mapper;
        return $this;
    }

    public function fetchOnlyFirst(): self
    {
        $this->fetchOnlyFirst = true;
        return $this;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    public function getFiltersEnabled(): bool
    {
        return $this->filtersEnabled;
    }

    public function getExcludeFilters(): array
    {
        return $this->excludeFilters;
    }

    public function getDefaultFilters(): array
    {
        return $this->defaultFilters;
    }

    public function getBaseQuery(): Builder
    {
        return $this->baseQuery;
    }

    public function getFilterTypeResolver(): array
    {
        return $this->filterTypeResolver;
    }

    public function getFilterNameMapping(): array
    {
        return $this->filterNameMapping;
    }

    public function hasFilterResolver(): bool
    {
        return !empty($this->filterNameMapping);
    }

    public function hasTypeResolver(): bool
    {
        return !empty($this->hasTypeResolver);
    }

    public function hasVariations(): bool
    {
        return !empty($this->modifiers);
    }

    public function shouldFetchOnlyFirst(): bool
    {
        return $this->fetchOnlyFirst;
    }

    public function getFilterProvider(): FilterProvider
    {
        return $this->filterProvider;
    }

    public function getPaginationProvider(): PaginationProvider
    {
        return $this->paginationProvider;
    }

    public function getVariationProvider(): VariationProvider
    {
        return $this->variationProvider;
    }

    public function getOrderingProvider(): OrderingProvider
    {
        return $this->orderingProvider;
    }

    public function getAggregation(): ?callable
    {
        return $this->aggregation;
    }

    public function getResultMapper(): ?Closure
    {
        return $this->resultMapper;
    }

    public function getResultOrderBy(): ?Closure
    {
        return $this->resultOrderBy;
    }

    public function getResultAllEntriesMapper(): ?Closure
    {
        return $this->resultAllEntriesMapper;
    }

    public function getShouldLogQuery(): bool
    {
        return $this->shouldLogQuery;
    }
}
