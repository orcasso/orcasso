<?php

namespace App\Dev\DataFixtures;

use App\Entity\Configuration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @codeCoverageIgnore
 */
class ConfigurationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_NAME))->setValue('École de musique intercommunale'));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_TYPE))->setValue('Association loi 1901 – Non assujettie à la TVA'));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_SIRET))->setValue($faker->siret));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_PHONE_NUMBER))->setValue($faker->phoneNumber));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_EMAIL))->setValue('ecole-de-musique@domain.net'));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_WEBSITE))->setValue('https://domain.net'));
        $manager->persist((new Configuration(Configuration::ITEM_ASSOCIATION_FULL_ADDRESS))->setValue(
            implode(\PHP_EOL, [$faker->streetAddress(), $faker->secondaryAddress, $faker->postcode.' '.$faker->city])
        ));
        $manager->persist((new Configuration(Configuration::ITEM_HOMEPAGE_INTRODUCTION))
            ->setValue('<b>Bienvenue</b> sur notre plateforme d\'inscription<br />Veuillez sélectionner votre adhésion'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION))
            ->setValue('Chèque à l\'ordre de "École de musique"'.\PHP_EOL.'à déposer dans la boite aux lettres'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_OTHER_INSTRUCTION))
            ->setValue('Possibilité de payer par espèce ou carte Top départ'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN))->setValue('FR76 1234 5123 4501 2345 6789 006'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC))->setValue('AAAAFRPPAAA'));
        $manager->persist((new Configuration(Configuration::ITEM_HELLOASSO_CLIENT_ID))->setValue('cc1clientid'));
        $manager->persist((new Configuration(Configuration::ITEM_HELLOASSO_CLIENT_SECRET))->setValue('SGUCLIENTSECRET'));
        $manager->persist((new Configuration(Configuration::ITEM_HELLOASSO_ASSO_SLUG))->setValue('test-asso-slug'));
        $manager->flush();
    }
}
