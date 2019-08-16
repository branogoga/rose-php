<?php

declare(strict_types=1);

require_once("Color.php");

final class ColorTest extends PHPUnit\Framework\TestCase
{
	public function		testGradient()
	{
        $gradient = \Rose\Utils\Color::calculateGradient("FF0000", "00FF77", 8);

		$this->assertEquals( 
            8,
            count($gradient)
        );

        $this->assertEquals(
            "ff0000",
            $gradient[0]
        );

        $this->assertEquals(
            "00ff77",
            $gradient[7]
        );

        $this->assertEquals(
            "da2411",
            $gradient[1]
        );

        $this->assertEquals(
            "48b655",
            $gradient[5]
        );

	}        
}

