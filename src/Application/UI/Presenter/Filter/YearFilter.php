<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

class YearFilter implements Filter
{
    private string $column;
    private string $key;

    public function __construct(string $column, string $key)
    {
        $this->column = $column;
        $this->key = $key;
    }

    public function getName(): string
    {
        return "RangeFilter-".$this->column."-".$this->key;
    }

    public function isValid(array $params): bool
    {
        if(!array_key_exists($this->key, $params)) 
        {
            return false;
        }

        $value = $params[$this->key];
        if(!is_numeric($value))
        {
            return false;
        }

        return true;
    }

    public function applyFilterToQuery(\Dibi\Fluent& $query, array $params): void
    {
        $value = (int)$params[$this->key];

        $dateFrom = \Rose\Utils\DateTimeHelper::timeRestrictionFrom($value);
        $query->where($this->column.">=%d",$dateFrom);

        $dateTo = \Rose\Utils\DateTimeHelper::timeRestrictionTo($value);
        $query->where($this->column."<=%d",$dateTo);
    }
}
