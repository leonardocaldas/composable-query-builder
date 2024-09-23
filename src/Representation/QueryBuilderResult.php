<?php

namespace ComposableQueryBuilder\Representation;

use ComposableQueryBuilder\QueryBuilderParams;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

readonly class QueryBuilderResult implements Jsonable
{
    public function __construct(
        private QueryBuilderParams $parameters,
        private Builder $builder,
    ) {}

    public function getParams(): QueryBuilderParams
    {
        return $this->parameters;
    }

    public function first(): mixed
    {
        $this->log();

        return $this->getBuilder()->first();
    }

    private function log(): void
    {
        if ($this->parameters->getShouldLogQuery()) {
            Log::info("process=composable_query_builder", [
                'query' => Str::replaceArray('?', $this->builder->getBindings(), $this->builder->toSql()),
            ]);
        }
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        try {
            $this->log();

            if (!$this->parameters->getPaginationProvider()->shouldPaginate()) {
                return $this->get();
            }

            return json_encode([
                'per_page'   => $this->parameters->getPaginationProvider()->getRowsPerPage(),
                'page'       => $this->parameters->getPaginationProvider()->getPage(),
                'total'      => $this->builder->getCountForPagination(),
                'rows'       => $this->get(),
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

    private function get(): Collection
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

    public function getUntouched(): Collection
    {
        $this->log();

        return $this->getBuilder()->get();
    }
}
