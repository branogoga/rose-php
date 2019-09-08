<?php

class	Owner_Assertion
{
    public function assert(
        \Nette\Security\Permission $acl,
        string $role,
        string $resource,
        string $privilege
        ): bool
    {
        $resource   = $acl->getQueriedResource();
        $user       = $resource->getUser();
        $userId 	= $user->getId();
        $ownerId 	= $resource->ownerId;

        $result = ($userId === $ownerId);

        return $result;
    }	
}
