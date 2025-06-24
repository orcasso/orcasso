<?php

namespace App\Entity;

interface MemberLogObjectInterface
{
    public function getLogConcernedMember(): Member;
}
