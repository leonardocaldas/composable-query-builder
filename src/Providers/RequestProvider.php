<?php

namespace ComposableQueryBuilder\Providers;

use ComposableQueryBuilder\Providers\Contracts\FilterProvider;
use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\Providers\Contracts\PaginationProvider;
use ComposableQueryBuilder\Providers\Contracts\ModifierProvider;
use Illuminate\Http\Request;

class RequestProvider implements FilterProvider, PaginationProvider, ModifierProvider, OrderingProvider
{
    private Request $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function getFilters(): array
    {
        return $this->request->except("page", "fields", "limit", "query", "modifier", "order_by", "XDEBUG_SESSION_START");
    }

    public function getPage(): int
    {
        return $this->request->get("page", 1);
    }

    public function getRowsPerPage(): int
    {
        return $this->request->get("limit", 10);
    }

    public function getModifier()
    {
        return $this->request->input("modifier");
    }

    public function shouldPaginate(): bool
    {
        return $this->request->has("page") && !$this->request->is("/export");
    }

    public function hasModifier(): bool
    {
        return !empty($this->getModifier());
    }

    public function hasOrderBy(): bool
    {
        return $this->request->has("order_by");
    }

    public function getFieldName(): string
    {
        return data_get($this->request->get("order_by"), 'field');
    }

    public function getSortDirection(): string
    {
        return data_get($this->request->get("order_by"), 'direction');
    }
}
