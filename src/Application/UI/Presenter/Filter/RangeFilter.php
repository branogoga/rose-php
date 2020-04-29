<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

class RangeFilter implements Filter
{
    private string $column;
    private string $minKey;
    private string $maxKey;

    public function __construct(string $column, string $minValueKey, string $maxValueKey)
    {
        $this->column = $column;
        $this->minKey = $minValueKey;
        $this->maxKey = $maxValueKey;
        $this->minFilter = new SingleValueIntegerFilter($minValueKey, $column, ">=");
        $this->maxFilter = new SingleValueIntegerFilter($maxValueKey, $column, "<=");
    }

    public function getName(): string
    {
        return "RangeFilter-".$this->column."-".$this->minKey."-".$this->maxKey;
    }

    public function isValid(array $params): bool
    {
        return $this->minFilter->isValid($params)
            && $this->maxFilter->isValid($params);
    }

    public function applyFilterToQuery(\Dibi\Fluent& $query, array $params): void
    {
        $this->minFilter->applyFilterToQuery($query, $params);
        $this->maxFilter->applyFilterToQuery($query, $params);
    }

    private SingleValueIntegerFilter $minFilter;
    private SingleValueIntegerFilter $maxFilter;
}

