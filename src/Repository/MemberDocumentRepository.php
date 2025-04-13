<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\MemberDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends ServiceEntityRepository<MemberDocument>
 */
class MemberDocumentRepository extends AbstractRepository
{
    public const ENTITY_CLASS = MemberDocument::class;

    public function __construct(ManagerRegistry $registry, protected string $appDirectoryMemberDocumentStorage,
        protected SluggerInterface $slugger)
    {
        parent::__construct($registry);
    }

    public function isRemovable(object $entity): bool
    {
        $this->checkSupport($entity);

        return true;
    }

    public function createFromUploadedFile(Member $member, string $name, UploadedFile $file): MemberDocument
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $document
            ->setFileName($newFilename)
            ->setFileSize($file->getSize())
            ->setMimeType((new MimeTypes())->guessMimeType($file->getPathname()))
        ;

        $file->move($this->getDirectory(), $newFilename);
        $this->update($document);

        return $document;
    }

    public function getContent(MemberDocument $document): false|string
    {
        if (!is_file($filename = $this->getFilePath($document))) {
            return false;
        }

        return file_get_contents($filename);
    }

    public function removeFile(MemberDocument $document): void
    {
        if (!is_file($filename = $this->getFilePath($document))) {
            return;
        }

        unlink($filename);
    }

    protected function getFilePath(MemberDocument $document): string
    {
        return \sprintf('%s/%s', $this->getDirectory(), $document->getFileName());
    }

    protected function getDirectory(): string
    {
        return $this->appDirectoryMemberDocumentStorage;
    }
}
