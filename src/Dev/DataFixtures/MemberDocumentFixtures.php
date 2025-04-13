<?php

namespace App\Dev\DataFixtures;

use App\Entity\Member;
use App\Entity\MemberDocument;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @codeCoverageIgnore
 */
class MemberDocumentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($manager->getRepository(Member::class)->findAll() as $member) {
            if (random_int(0, 1)) {
                continue;
            }

            copy(__DIR__.'/data/quotient_familial.jpeg', $tempFileName = tempnam(sys_get_temp_dir(), 'quotient_familial'));
            $file = new File($tempFileName);
            $uploadedFile = new UploadedFile($tempFileName, basename($tempFileName), null, null, true);

            $document = new MemberDocument($member);
            $document->setName('Quotient familial');
            $manager->getRepository(MemberDocument::class)
                ->storeFromUploadedFile($document, $uploadedFile);
        }
    }

    public function getDependencies(): array
    {
        return [MemberFixtures::class];
    }
}
