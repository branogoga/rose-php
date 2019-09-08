<?php

declare(strict_types=1);

final class ModelTest extends PHPUnit\Framework\TestCase
{
     public function testGetTable(): void
    {
        $model = new TestModel();
        self::assertEquals(
            " table-name AS tmp ",
            $model->getTable()
        );
    }
}

