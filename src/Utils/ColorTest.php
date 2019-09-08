<?php

declare(strict_types=1);

final class ColorTest extends PHPUnit\Framework\TestCase
{
	public function		testGradient(): void
	{
        $gradient = \Rose\Utils\Color::calculateGradient("FF0000", "00FF77", 8);

		self::assertEquals( 
            8,
            count($gradient)
        );

        self::assertEquals(
            "ff0000",
            $gradient[0]
        );

        self::assertEquals(
            "00ff77",
            $gradient[7]
        );

        self::assertEquals(
            "da2411",
            $gradient[1]
        );

        self::assertEquals(
            "48b655",
            $gradient[5]
        );

	}        
}

