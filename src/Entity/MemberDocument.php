<?php

namespace App\Entity;

use App\Repository\MemberDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MemberDocumentRepository::class)]
class MemberDocument
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    protected Member $member;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected string $name = '';

    #[ORM\Column(name: 'file_name', type: 'string', length: 255)]
    protected string $fileName;

    #[ORM\Column(name: 'file_size', type: 'integer')]
    protected int $fileSize = 0;

    #[ORM\Column(name: 'mime_type', type: 'string', length: 255)]
    protected string $mimeType;

    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
