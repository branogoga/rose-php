<?php
declare(strict_types=1);

require_once("Math.php");

final class MathTest extends PHPUnit\Framework\TestCase
{
    public function testAdd(): void
    {
        $this->assertEquals(
            3,
            Math::add(1, 2)
        );
    }
}