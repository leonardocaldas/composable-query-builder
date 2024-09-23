<?php

namespace ComposableQueryBuilder;

use ComposableQueryBuilder\Providers\Contracts\FilterProvider;
use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\Providers\Contracts\PaginationProvider;
use ComposableQueryBuilder\Providers\Contracts\VariationProvider;
use ComposableQueryBuilder\Providers\RequestProvider;
use ComposableQueryBuilder\Representation\QueryVariation;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class ComposableQueryBuilderParams
{
    private Builder $baseQuery;

    private array $filterResolver = [];

    private array $filterTypeResolver = [];

    /**
     * @var QueryVariation[] $variations
     */
    private $variations = [];

    /**
     * @var Map<String, Callable<Builder>> $defaultFilters
     */
    private $defaultFilters = [];

    /**
     * @var array Map<String, Callable<Builder>> $overrideFilters
     */
    private array $overrideFilters = [];

    /**
     * @var callable $aggregation
     */
    private $aggregation;

    private ?Closure $resultMapper           = null;
    private ?Closure $resultAllEntriesMapper = null;
    private ?Closure $resultOrderBy = null;

    private FilterProvider $filterProvider;

    private OrderingProvider $orderingProvider;

    private PaginationProvider $paginationProvider;

    private VariationProvider $variationProvider;

    private bool  $automaticFiltersEnabled = true;
    private array $allowedFilters          = [];
    private array $notAllowedFilters       = ['token'];

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

    public function withQuery($builder): self
    {
        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $builder = $builder->toBase();
        }

        $this->baseQuery = $builder;
        return $this;
    }

    public function withBaseQuery(Builder $builder): self
    {
        $this->baseQuery = $builder;
        return $this;
    }

    public function withPaginationProvider(PaginationProvider $paginationProvider): self
    {
        $this->paginationProvider = $paginationProvider;
        return $this;
    }

    public function withFilterProvider(FilterProvider $filterProvider): self
    {
        $this->filterProvider = $filterProvider;
        return $this;
    }

    public function withVariationProvider(VariationProvider $variationProvider): self
    {
        $this->variationProvider = $variationProvider;
        return $this;
    }

    public function withOrderingProvider(OrderingProvider $orderingProvider): self
    {
        $this->orderingProvider = $orderingProvider;
        return $this;
    }

    public function withFilterResolver(array $filterResolver): self
    {
        $this->filterResolver = $filterResolver;
        return $this;
    }

    public function logQuery(): self
    {
        $this->shouldLogQuery = true;
        return $this;
    }

    public function withFilterTypeResolver(array $typeResolver): self
    {
        $this->filterTypeResolver = $typeResolver;
        return $this;
    }

    public function withNotAllowedFilters(array $filters): self
    {
        $this->notAllowedFilters = array_merge($this->notAllowedFilters, $filters);
        return $this;
    }

    public function disableAutomaticFilters(): self
    {
        $this->automaticFiltersEnabled = false;
        return $this;
    }

    public function addFilterResolversToAllowedFilters(): self
    {
        return $this->withAllowedFilters(
            array_values($this->filterResolver)
        );
    }

    public function withAllowedFilters(array $filters): self
    {
        $this->allowedFilters = array_merge($this->allowedFilters, $filters);
        return $this;
    }

    public function withVariation($name, callable $callable, $default = false): self
    {
        $this->variations[] = new QueryVariation($name, $callable, $default);
        return $this;
    }

    public function withAggregation(callable $callable): self
    {
        $this->aggregation = $callable;
        return $this;
    }

    public function withDefaultFilter($filterName, callable $callable): self
    {
        $this->defaultFilters[$filterName] = $callable;
        return $this;
    }

    public function withOverrideFilter($filterName, callable $callable): self
    {
        $this->overrideFilters[$filterName] = $callable;
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

    public function getVariations(): array
    {
        return $this->variations;
    }

    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    public function getAutomaticFiltersEnabled(): bool
    {
        return $this->automaticFiltersEnabled;
    }

    public function getNotAllowedFilters(): array
    {
        return $this->notAllowedFilters;
    }

    public function getDefaultFilters(): array
    {
        return $this->defaultFilters;
    }

    public function getOverrideFilters(): array
    {
        return $this->overrideFilters;
    }

    public function getBaseQuery(): Builder
    {
        return $this->baseQuery;
    }

    public function getFilterTypeResolver(): array
    {
        return $this->filterTypeResolver;
    }

    public function getFilterResolver(): array
    {
        return $this->filterResolver;
    }

    public function hasFilterResolver(): bool
    {
        return !empty($this->filterResolver);
    }

    public function hasTypeResolver(): bool
    {
        return !empty($this->hasTypeResolver);
    }

    public function hasVariations(): bool
    {
        return !empty($this->variations);
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
