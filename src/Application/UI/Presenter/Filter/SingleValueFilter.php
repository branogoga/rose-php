<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

abstract class SingleValueFilter implements Filter
{
    public function __construct(string $key)
    {
        $this->key = $key;        
    }

    public function isValid(array $params): bool
    {
        if(!array_key_exists($this->key, $params))
        {
            return false;
        }

        return true;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    protected string $key;
}
