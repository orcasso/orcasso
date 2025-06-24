<?php

namespace App\Utils;

use App\Entity\MemberLog;
use App\Entity\MemberLogObjectInterface;

class LoggableListener extends \Gedmo\Loggable\LoggableListener
{
    /**
     * @param MemberLog                $logEntry
     * @param MemberLogObjectInterface $object
     */
    protected function prePersistLogEntry($logEntry, $object): void
    {
        $logEntry->setMember($object->getLogConcernedMember());
    }
}
