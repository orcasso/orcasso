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
    public const INSTRUMENTS = [
        'Chant',
        'Batterie',
        'Clarinette',
        'Flûte traversière',
        'Guitare',
        'Guitare électrique',
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
        'Initiation musicale (5-6 ans)',
        'Chorale enfants (7-14 ans)',
        'Atelier musiques actuelles ado',
        'Atelier musiques actuelles adultes',
        'Atelier vocal adulte',
        'Atelier musique à l\'image',
        'Atelier de rythmique corporelle',
        'Guitare - voix',
        'Orchestre à danser',
        'Batucada',
    ];

    public const CHOIR = [
        'Choeur Terpsichore',
    ];

    public static function activities(): array
    {
        return array_merge(
            static::INSTRUMENTS,
            static::FM,
            static::COLLECTIVES,
            static::CHOIR,
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
