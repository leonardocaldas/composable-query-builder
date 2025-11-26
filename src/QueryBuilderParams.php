<?php

namespace ComposableQueryBuilder;

use Closure;
use ComposableQueryBuilder\Providers\Contracts\FilterProvider;
use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\Providers\Contracts\PaginationProvider;
use ComposableQueryBuilder\Providers\Contracts\ModifierProvider;
use ComposableQueryBuilder\Providers\RequestProvider;
use ComposableQueryBuilder\Representation\QueryModifier;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class QueryBuilderParams
{
    private Builder $baseQuery;

    private array $filterNameMapping = [];
    private array $orderByNameMapping = [];

    private array $filterBehavior = [];

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
    private ?string  $filterNameDefaultTable = null;

    private FilterProvider $filterProvider;

    private OrderingProvider $orderingProvider;

    private PaginationProvider $paginationProvider;

    private ModifierProvider $variationProvider;

    private bool  $filtersEnabled = true;
    private array $allowedFilters = [];
    private array $excludeFilters = ['token'];

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
        return new QueryBuilderParams();
    }

    public static function newFromRequest(Request $request): self
    {
        return new QueryBuilderParams(
            new RequestProvider($request)
        );
    }

    public function forQuery($builder): self
    {
        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $builder = $builder->toBase();
        }

        $this->baseQuery = $builder;
        return $this;
    }

    public function setModifierProvider(ModifierProvider $variationProvider): self
    {
        $this->variationProvider = $variationProvider;
        return $this;
    }

    public function filterNameDefaultTable(string $tableName): self
    {
        $this->filterNameDefaultTable = $tableName;
        return $this;
    }

    public function filterNameMapping(array $mapping): self
    {
        $this->filterNameMapping = $mapping;
        return $this;
    }

    public function orderByNameMapping(array $mapping): self
    {
        $this->orderByNameMapping = $mapping;
        return $this;
    }

    public function logQuery(): self
    {
        $this->shouldLogQuery = true;
        return $this;
    }

    public function filterBehavior(array $typeResolver): self
    {
        $this->filterBehavior = $typeResolver;
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

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    public function setAllowedFilters(array $filters): self
    {
        $this->allowedFilters = array_merge($this->allowedFilters, $filters);
        return $this;
    }

    public function getFiltersEnabled(): bool
    {
        return $this->filtersEnabled;
    }

    public function getExcludeFilters(): array
    {
        return $this->excludeFilters;
    }

    public function setExcludeFilters(array $filters): self
    {
        $this->excludeFilters = array_merge($this->excludeFilters, $filters);
        return $this;
    }

    public function getDefaultFilters(): array
    {
        return $this->defaultFilters;
    }

    public function getBaseQuery(): Builder
    {
        return $this->baseQuery;
    }

    public function getFilterBehavior(): array
    {
        return $this->filterBehavior;
    }

    public function getFilterNameMapping(): array
    {
        return $this->filterNameMapping;
    }

    public function getOrderByNameMapping(): array
    {
        return $this->orderByNameMapping;
    }

    public function getFilterNameDefaultTable(): ?string
    {
        return $this->filterNameDefaultTable;
    }

    public function hasFilterResolver(): bool
    {
        return !empty($this->filterNameMapping);
    }

    public function hasTypeResolver(): bool
    {
        return !empty($this->hasTypeResolver);
    }

    public function hasModifier(): bool
    {
        return !empty($this->modifiers);
    }

    public function getFilterProvider(): FilterProvider
    {
        return $this->filterProvider;
    }

    public function setFilterProvider(FilterProvider $filterProvider): self
    {
        $this->filterProvider = $filterProvider;
        return $this;
    }

    public function getPaginationProvider(): PaginationProvider
    {
        return $this->paginationProvider;
    }

    public function setPaginationProvider(PaginationProvider $paginationProvider): self
    {
        $this->paginationProvider = $paginationProvider;
        return $this;
    }

    public function getModifierProvider(): ModifierProvider
    {
        return $this->variationProvider;
    }

    public function getOrderingProvider(): OrderingProvider
    {
        return $this->orderingProvider;
    }

    public function setOrderingProvider(OrderingProvider $orderingProvider): self
    {
        $this->orderingProvider = $orderingProvider;
        return $this;
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
