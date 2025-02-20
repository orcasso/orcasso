<?php

namespace App\Dev\DataFixtures;

use App\Entity\Activity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class ActivityFixtures extends Fixture
{
    public const ACTIVITIES = [
        'Éveil musical',
        'Initiation',
        // Instruments
        'Chant',
        'Batterie',
        'Clarinette',
        'Flûte traversière',
        'Guitare',
        'Basse',
        'Piano',
        'Violon',
        // FM
        'Formation musicale 1C1',
        'Formation musicale 1C2',
        'Formation musicale 1C3',
        'Formation musicale 1C4',
        'Formation musicale 2C',
        // Collectives
        'Choeur Terpsichore',
        'Atelier musiques actuelles ado',
        'Atelier musiques actuelles adultes',
        'Atelier vocal adulte',
        // Cotisations
        'Cotisation familiale',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (static::ACTIVITIES as $index => $name) {
            $activity = new Activity();
            $manager->persist($activity
                ->setName(static::getCompleteName($name))
            );
            $this->addReference('activity_'.$name, $activity);
        }
        $manager->flush();
    }

    public static function getCompleteName(string $name): string
    {
        $year = (int) date_create_immutable('-7 months')->format('Y');

        return \sprintf('%d/%d %s', $year, $year + 1, $name);
    }
}
