<?php

declare(strict_types=1);

final class ResourceTest extends PHPUnit\Framework\TestCase
{
    public function testGetResourceId(): void
    {
        $resource = new \Rose\Security\Resource("#testID");
        self::assertEquals(
            "#testID",
            $resource->getResourceId()
        );
    }
}