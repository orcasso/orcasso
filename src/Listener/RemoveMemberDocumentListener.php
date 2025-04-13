<?php

namespace App\Listener;

use App\Entity\MemberDocument;
use App\Repository\MemberDocumentRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postRemove)]
class RemoveMemberDocumentListener
{
    public function __construct(protected MemberDocumentRepository $repository)
    {
    }

    public function postRemove(PostRemoveEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof MemberDocument) {
            return;
        }

        $this->repository->removeFile($entity);
    }
}
