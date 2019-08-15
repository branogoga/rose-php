<?php

declare(strict_types=1);

require_once("SayHello.php");

final class SayHelloTest extends PHPUnit\Framework\TestCase
{
    public function testSayHello(): void
    {
        $this->assertEquals(
            "Hello World, Composer!",
            \Rose\HelloWorld\SayHello::world()
        );
    }
}