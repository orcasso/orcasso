<?php

namespace App\Utils;

use Gedmo\Tool\ActorProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LogActorProvider implements ActorProviderInterface
{
    public function __construct(protected Security $security)
    {
    }

    public function getActor(): string
    {
        return $this->security->getUser()?->getUserIdentifier() ?? '';
    }
}
