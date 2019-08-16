<?php
declare(strict_types=1);

final class MathTest extends PHPUnit\Framework\TestCase
{
    public function testAdd(): void
    {
        $this->assertEquals(
            3,
            Rose\Math::add(1, 2)
        );
    }
}