<?php

interface OwnedResourceInterface extends \Nette\Security\IResource
{
    public function getUser(): \Nette\Security\User; 
    public function getResourceId(): string;
}

abstract class OwnedResource implements OwnedResourceInterface
{
    /**
     * @var \Nette\Security\User
     */
    private $user;
    
    public function __construct( \Nette\Security\User $user )
    {
        $this->user = $user;
    }
    
    public function getUser(): \Nette\Security\User
    {
        return $this->user;
    }
}
