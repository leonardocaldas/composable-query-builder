<?php

namespace ComposableQueryBuilder\Representation;

class QueryVariation
{
    private string $name;
    private $callable;
    private bool $default;

    public function __construct($name, callable $callable, $default = false)
    {
        $this->name     = $name;
        $this->callable = $callable;
        $this->default  = $default;
    }

    public function isDefault(): bool
    {
        return (bool)$this->default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
