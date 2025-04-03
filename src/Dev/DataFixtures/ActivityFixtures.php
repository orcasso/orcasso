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
    public const FOR_THE_LITTLE_ONES = [
        'Éveil musical',
        'Initiation',
    ];

    public const INSTRUMENTS = [
        'Chant',
        'Batterie',
        'Clarinette',
        'Flûte traversière',
        'Guitare',
        'Basse',
        'Piano',
        'Violon',
    ];

    public const FM = [
        'Formation musicale 1C1',
        'Formation musicale 1C2',
        'Formation musicale 1C3',
        'Formation musicale 1C4',
        'Formation musicale 2C',
    ];

    public const COLLECTIVES = [
        'Choeur Terpsichore',
        'Atelier musiques actuelles ado',
        'Atelier musiques actuelles adultes',
        'Atelier vocal adulte',
    ];

    public static function activities(): array
    {
        return array_merge(
            static::FOR_THE_LITTLE_ONES,
            static::INSTRUMENTS,
            static::FM,
            static::COLLECTIVES,
            ['Cotisation familiale']
        );
    }

    public function load(ObjectManager $manager): void
    {
        foreach (static::activities() as $index => $name) {
            $activity = (new Activity())->setName($name);
            $manager->persist($activity);
            $this->addReference($name, $activity);
        }
        $manager->flush();
    }
}
