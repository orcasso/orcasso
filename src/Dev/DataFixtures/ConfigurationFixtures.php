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
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION))
            ->setValue('Chèque à l\'ordre de "École de musique"'.\PHP_EOL.'à déposer dans la boite aux lettres'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_IBAN))->setValue('FR76 1234 5123 4501 2345 6789 006'));
        $manager->persist((new Configuration(Configuration::ITEM_PAYMENT_METHOD_BANK_TRANSFER_BIC))->setValue('AAAAFRPPAAA'));
        $manager->flush();
    }
}
