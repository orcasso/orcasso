<?php

namespace App\Entity;

use App\Repository\MemberLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

#[ORM\Table(name: 't_member_log')]
#[ORM\Entity(repositoryClass: MemberLogRepository::class)]
class MemberLog extends AbstractLogEntry
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', nullable: false)]
    protected Member $member;

    #[ORM\Column(type: 'json', nullable: true)]
    protected $data = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }
}
