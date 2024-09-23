<?php

namespace ComposableQueryBuilder\Representation;

use ComposableQueryBuilder\ComposableQueryBuilderParams;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ComposableQueryBuilderResult implements Jsonable
{
    private ComposableQueryBuilderParams $parameters;
    private Builder                      $builder;

    public function __construct(ComposableQueryBuilderParams $parameters, Builder $builder)
    {
        $this->parameters = $parameters;
        $this->builder    = $builder;
    }

    public function getParameters(): ComposableQueryBuilderParams
    {
        return $this->parameters;
    }

    public function getResults(): Collection
    {
        $this->logIfNeeded();
        return $this->getBuilder()->get();
    }

    private function logIfNeeded()
    {
        if ($this->parameters->getShouldLogQuery()) {
            Log::info("process=parametrized_query", [
                'query' => str_replace_array('?', $this->builder->getBindings(), $this->builder->toSql()),
            ]);
        }
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function fetchFirst()
    {
        $this->logIfNeeded();
        return $this->getBuilder()->first();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        try {
            $this->logIfNeeded();

            if ($this->parameters->shouldFetchOnlyFirst()) {
                return json_encode($this->builder->first(), $options);
            }

            if (!$this->parameters->getPaginationProvider()->shouldPaginate()) {
                return $this->getRows();
            }

            return json_encode([
                'per_page'   => $this->parameters->getPaginationProvider()->getRowsPerPage(),
                'page'       => $this->parameters->getPaginationProvider()->getPage(),
                'total'      => $this->builder->getCountForPagination(),
                'rows'       => $this->getRows(),
                'aggregates' => $this->getAggregates(),
            ], $options);
        } catch (Throwable $throwable) {
            Log::error("process=parameterized_query, status=failed", [
                'sql'       => $this->builder->toSql(),
                'exception' => $throwable,
            ]);

            throw $throwable;
        }
    }

    private function getRows(): Collection
    {
        $result            = $this->builder->get();
        $singleEntryMapper = $this->parameters->getResultMapper();
        $resultOrderBy     = $this->parameters->getResultOrderBy();
        $allEntriesMapper  = $this->parameters->getResultAllEntriesMapper();

        if ($allEntriesMapper) {
            $result = $allEntriesMapper($result);
        }

        if ($singleEntryMapper) {
            $result = $result->map($singleEntryMapper);
        }

        if ($resultOrderBy) {
            $result = $resultOrderBy($result);
        }

        return $result;
    }

    public function getAggregates(): array
    {
        $aggregation = $this->parameters->getAggregation();

        if (!is_null($aggregation) && is_callable($aggregation)) {
            return $aggregation($this->builder->cloneWithout(['limit', 'offset', 'groups']));
        }

        return [];
    }
}
