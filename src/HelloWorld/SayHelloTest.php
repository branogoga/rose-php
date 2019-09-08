<?php

declare(strict_types=1);

final class SayHelloTest extends PHPUnit\Framework\TestCase
{
    public function testSayHello(): void
    {
        self::assertEquals(
            "Hello World, Composer!",
            \Rose\HelloWorld\SayHello::world()
        );
    }
}