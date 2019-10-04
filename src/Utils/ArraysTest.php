<?php

declare(strict_types=1);

final class ArraysTest extends PHPUnit\Framework\TestCase
{
	public function		testToArray(): void
	{
        $input = new \Nette\Utils\ArrayHash();
        $input[1] = "jedna";
        $input[7] = 17;
        $input["key"] = "value";

        $output = \Rose\Utils\Arrays::toArray($input);

        //self::assertTrue(is_array($output));
        self::assertEquals(count($input), count($output));

        self::assertArrayHasKey(1, $output);
        self::assertEquals("jedna", $output[1]);

        self::assertArrayHasKey(7, $output);
        self::assertEquals(17, $output[7]);

        self::assertArrayHasKey("key", $output);
        self::assertEquals("value", $output["key"]);
	}        
}

