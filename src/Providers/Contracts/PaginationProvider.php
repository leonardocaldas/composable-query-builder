<?php

namespace ComposableQueryBuilder\Providers\Contracts;

interface PaginationProvider
{
    public function shouldPaginate(): bool;

    public function getPage(): int;

    public function getRowsPerPage(): int;
}
