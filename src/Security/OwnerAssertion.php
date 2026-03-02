<?php

namespace Rose\Security;

class	OwnerAssertion
{
    public function assert(
        \Nette\Security\Permission $acl,
        string $role,
        string $resource,
        string $privilege
        ): bool
    {
        $resource   = $acl->getQueriedResource();
        if (!$resource instanceof \Rose\Security\OwnedResourceInterface) {
            return false;
        }
        $user       = $resource->getUser();
        $userId 	= $user->getId();
        /** @phpstan-ignore property.notFound */
        $ownerId 	= $resource->ownerId;

        $result = ($userId === $ownerId);

        return $result;
    }	
}
