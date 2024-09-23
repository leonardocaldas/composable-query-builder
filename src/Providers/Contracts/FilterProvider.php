<?php

namespace ComposableQueryBuilder\Providers\Contracts;

interface FilterProvider
{
    public function getFilters(): array;
}
