<?php
declare(strict_types=1);

use \PHPUnit\Framework\TestCase;

final class MathTest extends TestCase
{
    public function testAdd(): void
    {
        $this->assertEquals(
            3,
            Math::add(1, 2)
        );
    }
}