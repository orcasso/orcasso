<?php

namespace App\Dev\DataFixtures;

use App\Entity\Activity;
use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class OrderFormFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->createFormChildFullCourse($manager);
        $this->createFormChildDoubleCourses($manager);
        $this->createFormChildOthers($manager);
        $manager->flush();
    }

    protected function createFormChildFullCourse(ObjectManager $manager): void
    {
        $form = new OrderForm();
        $form
            ->setTitle('Cursus complet enfant (-18 ans)')
            ->setDescription('Le cursus complet comprend : '.\PHP_EOL.'- La pratique instrumentale ou vocale'.\PHP_EOL.
                '- La formation musicale (obligatoire)'.\PHP_EOL.'- La pratique collective (facultative)')
            ->setOrderMainLineLabel('Cursus complet enfant')
            ->setOrderMainLineAmount(695)
            ->setEnabled(true)
        ;
        $this->addReference('cursus_complet_enfant', $form);
        $manager->persist($form);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ACTIVITY_CHOICE)
            ->setQuestion('Choix de la pratique instrumentale ou vocale')
        ;
        foreach (ActivityFixtures::INSTRUMENTS as $instrument) {
            (new OrderFormFieldChoice($field))->setActivity($this->getReference($instrument, Activity::class));
        }

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ACTIVITY_CHOICE)
            ->setQuestion('Choix du cursus de formation musicale')
        ;
        foreach (ActivityFixtures::FM as $fm) {
            (new OrderFormFieldChoice($field))->setActivity($this->getReference($fm, Activity::class));
        }

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Quelle est votre commune ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune adhérente : Grane')->setAllowancePercentage(18);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune adhérente : Allex')->setAllowancePercentage(18);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune participante : Chabrillan')->setAllowancePercentage(5);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune participante : La Roche-sur-Grane')->setAllowancePercentage(5);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Autre commune')->setAllowancePercentage(0);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Est-ce le premier membre inscrit de la famille ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Oui')->setAllowancePercentage(0);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('2ème inscrit')->setAllowancePercentage(8);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('3ème inscrit')->setAllowancePercentage(10);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Quelle est votre quotient familial ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('QF < 500')->setAllowancePercentage(30);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('500 < QF < 900')->setAllowancePercentage(20);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('900 < QF < 1300')->setAllowancePercentage(10);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('QF > 1300')->setAllowancePercentage(0);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ACTIVITY_CHOICE)
            ->setQuestion('Merci de sélectionner la cotisation familiale si vous ne l\'avez pas encore réglé pour un autre membre de la famille.')
            ->setRequired(false)
        ;
        (new OrderFormFieldChoice($field))->setActivity($this->getReference('Cotisation familiale', Activity::class))->setActivityAmount(20);

        $manager->flush();
    }

    protected function createFormChildDoubleCourses(ObjectManager $manager): void
    {
        $form = new OrderForm();
        $form
            ->setTitle('Double cursus enfant (-18 ans)')
            ->setDescription('2 disciplines pour un même enfant')
            ->setOrderMainLineLabel('Double cursus enfant')
            ->setOrderMainLineAmount(1328)
            ->setEnabled(true)
        ;
        $this->addReference('double_cursus_enfant', $form);
        $manager->persist($form);

        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_enfant', OrderForm::class);
        $form1->getFields()[0]->clone($form);
        $form1->getFields()[0]->clone($form)->setQuestion('Choix de la seconde pratique instrumentale ou vocale');
        $form1->getFields()[1]->clone($form);
        $form1->getFields()[2]->clone($form);
        $form1->getFields()[4]->clone($form);
        $form1->getFields()[5]->clone($form);

        $manager->flush();
    }

    protected function createFormChildOthers(ObjectManager $manager): void
    {
        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_enfant', OrderForm::class);
        $form = $form1->clone();
        $form
            ->setTitle('Autre pratique enfant (-18 ans)')
            ->setDescription('')
            ->setOrderMainLineLabel('Autre pratique enfant')
            ->setOrderMainLineAmount(215)
            ->setEnabled(true)
        ;
        $manager->persist($form);

        $form->getFields()[0]->getChoices()->clear();
        $form->getFields()[0]->setQuestion('Choix de la pratique');
        foreach (ActivityFixtures::COLLECTIVES as $collective) {
            (new OrderFormFieldChoice($form->getFields()[0]))->setActivity($this->getReference($collective, Activity::class));
        }

        $manager->flush();

        $manager->remove($form->getFields()->get(1));
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ActivityFixtures::class];
    }
}
