<?php

declare(strict_types=1);

namespace Rose\Application\UI\Presenter\Filters;

class SingleValueIntegerFilter extends SingleValueFilter
{
    public function __construct(string $key, string $column, string $operator)
    {
        parent::__construct($key);
        $this->operator = $operator;
        $this->column = $column;
    }

    public function getName(): string
    {
        return "SingleValueIntegerFilter-".$this->getKey()."-".$this->column."-".$this->operator;
    }

    public function isValid(array $params): bool
    {
        if(!parent::isValid($params))
        {
            return false;
        }

        $value = $params[$this->key];
        return is_numeric($value);
    }

    public function applyFilterToQuery(\Dibi\Fluent& $query, array $params): void
    {
        if($this->isValid($params))
        {
            $value = $params[$this->key];
            $query->where($this->column.$this->operator."%i", (int)$value);
        }
    }

    private string $column;
    private string $operator;
}
