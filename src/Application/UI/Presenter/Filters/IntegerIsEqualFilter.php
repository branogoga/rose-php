<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

class IntegerIsEqualFilter extends SingleValueIntegerFilter
{
    public function __construct(string $key)
    {
        parent::__construct($key, $key, "=");
    }

    public function getName(): string
    {
        return "IntegerIsEqualFilter-".$this->key;
    }
}
