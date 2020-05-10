<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

interface Filter
{
    public function getName(): string;
    public function isValid(array $params): bool;
    public function applyFilterToQuery(\Dibi\Fluent& $query, array $params): void;
}

