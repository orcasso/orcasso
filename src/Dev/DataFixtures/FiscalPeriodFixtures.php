<?php

namespace App\Dev\DataFixtures;

use App\Entity\FiscalPeriod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class FiscalPeriodFixtures extends Fixture
{
    public const PAST = 'past';
    public const CURRENT = 'current';

    public function load(ObjectManager $manager): void
    {
        $currentYear = (int) date_create('-7 months')->format('Y');

        $past = (new FiscalPeriod())->setName(($currentYear - 1).'-'.$currentYear);
        $manager->persist($past);
        $this->addReference(self::PAST, $past);

        $current = (new FiscalPeriod())->setName($currentYear.'-'.($currentYear + 1))->setCurrent(true);
        $manager->persist($current);
        $this->addReference(self::CURRENT, $current);

        $manager->flush();
    }
}
