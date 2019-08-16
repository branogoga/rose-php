<?php

declare(strict_types=1);

class TestModel extends \Rose\Data\Model\Model 
{
    protected 	function	getTableName(): string
    {
        return "table-name";
    }

    public 	function    getPrimaryKeyName(): string
    {
        return "id-column-name";
    }

    public function getTable(): string
    {
        return parent::getTable();
    }

    public function getEmptyObject(): array 
    {
        $entity = array();
        $entity[$this->getPrimaryKeyName()] = 0;
        return $entity;
    }
}
