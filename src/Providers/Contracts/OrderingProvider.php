<?php

namespace ComposableQueryBuilder\Providers\Contracts;

interface OrderingProvider
{
    public function hasOrderBy(): bool;

    public function getFieldName(): string;

    public function getSortDirection(): string;
}
