<?php

declare(strict_types=1);

require_once("Model.php");

class TestModel extends \Rose\Data\Model\Model 
{
    protected 	function	getTableName(): string
    {
        return "table-name";
    }

    public 	function	getPrimaryKeyName(): string
    {
        return "id-column-name";
    }

    public function     getTable(): string
    {
        return parent::getTable();
    }
}

final class ModelTest extends PHPUnit\Framework\TestCase
{
     public function testGetTable(): void
    {
        $model = new TestModel();
        $this->assertEquals(
            " table-name AS tmp ",
            $model->getTable()
        );
    }
}

