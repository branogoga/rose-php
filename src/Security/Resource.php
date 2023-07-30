<?php

namespace Rose\Security;

class   Resource implements \Nette\Security\Resource
{
    public function __construct(string $resourceId)
    {
        $this->resourceId = $resourceId;        
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * @var string
     */
    private $resourceId;
}