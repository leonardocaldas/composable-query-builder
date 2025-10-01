<?php

namespace ComposableQueryBuilder\Providers;

use ComposableQueryBuilder\Providers\Contracts\FilterProvider;
use ComposableQueryBuilder\Providers\Contracts\OrderingProvider;
use ComposableQueryBuilder\Providers\Contracts\PaginationProvider;
use ComposableQueryBuilder\Providers\Contracts\VariationProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestProvider implements FilterProvider, PaginationProvider, VariationProvider, OrderingProvider
{
    private Request $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function getFilters(): array
    {
        $qs = $this->request->getQueryString();

        $fields = Str::of($qs)->explode('&');

        $dotted = collect($fields)
            ->filter(fn($item) => Str::contains($item, '.'))
            ->mapWithKeys(function($value) {
                $parts = explode('=', $value);
                return [$parts[0] => $parts[1]];
            })
            ->toArray();

        $notDotted = collect($fields)
            ->filter(fn($item) => ! Str::contains($item, '.'))
            ->join("&");

        $qs = [];
        parse_str($notDotted, $qs);

        return array_merge($qs, $dotted);
    }

    public function getPage(): int
    {
        return $this->request->get("page", 1);
    }

    public function getRowsPerPage(): int
    {
        return $this->request->get("limit", 10);
    }

    public function getVariation()
    {
        return $this->request->input("variation");
    }

    public function shouldPaginate(): bool
    {
        return $this->request->has("page") && !$this->request->is("/export");
    }

    public function hasVariation()
    {
        return !empty($this->getVariation());
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
