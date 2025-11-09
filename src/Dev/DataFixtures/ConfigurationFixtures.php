<?php

namespace App\Dev\DataFixtures;

use App\Entity\Configuration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class ConfigurationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
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
