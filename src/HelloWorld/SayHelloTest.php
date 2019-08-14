<?php

final class SayHelloTest extends PHPUnit\Framework\TestCase
{
    public function testSayHello(): void
    {
        $this->assertEquals(
            "Hello World, Composer!",
            \HelloWorld\SayHello::world()
        );
    }
}