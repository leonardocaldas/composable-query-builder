<?php

namespace ComposableQueryBuilder\Providers\Contracts;

interface ModifierProvider
{
    public function getModifier();

    public function hasModifier();
}
