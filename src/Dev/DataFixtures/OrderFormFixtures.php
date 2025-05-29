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
        $this->createFormAdultFullCourse($manager);
        $this->createFormAdultDoubleCourses($manager);
        $this->createFormAdultOthers($manager);
        $this->createFormAdultChoir($manager);
        $manager->flush();
    }

    protected function createFormChildFullCourse(ObjectManager $manager): void
    {
        $form = new OrderForm();
        $form
            ->setTitle('Cursus complet enfant (moins de 18 ans)')
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
            ->setType(OrderFormField::TYPE_ACTIVITY_CHOICE)
            ->setQuestion('Choix d\'une pratique collective (facultative)')
        ;
        foreach (ActivityFixtures::COLLECTIVES as $collective) {
            (new OrderFormFieldChoice($field))->setActivity($this->getReference($collective, Activity::class));
        }

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Quelle est votre commune ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune adhérente (Grane/Allex)')->setAllowancePercentage(17);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Commune participante (Chabrillan/Roche-sur-Grane)')->setAllowancePercentage(5);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Autre commune')->setAllowancePercentage(0);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Est-ce le premier membre inscrit de la famille ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Oui, premier inscrit')->setAllowancePercentage(0);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('2ème inscrit')->setAllowancePercentage(7);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('3ème inscrit')->setAllowancePercentage(10);

        $field = (new OrderFormField($form))
            ->setType(OrderFormField::TYPE_ALLOWANCE_CHOICE)
            ->setQuestion('Quelle est votre quotient familial ?')
        ;
        (new OrderFormFieldChoice($field))->setAllowanceLabel('QF < 500')->setAllowancePercentage(30);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('500 < QF < 900')->setAllowancePercentage(20);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('900 < QF < 1300')->setAllowancePercentage(10);
        (new OrderFormFieldChoice($field))->setAllowanceLabel('QF > 1300')->setAllowancePercentage(0);

        (new OrderFormField($form))->setType(OrderFormField::TYPE_DOCUMENT)->setRequired(false)
            ->setQuestion('Merci de fournir l\'attestation de quotient familial si inférieur à 1300.')
        ;

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
            ->setTitle('Double cursus enfant (moins de 18 ans)')
            ->setDescription('2 disciplines pour un même enfant')
            ->setOrderMainLineLabel('Double cursus enfant')
            ->setOrderMainLineAmount(1328)
            ->setEnabled(true)
        ;
        $this->addReference('double_cursus_enfant', $form);
        $manager->persist($form);

        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_enfant', OrderForm::class);
        foreach ($form1->getFields() as $index => $field) {
            if ('Est-ce le premier membre inscrit de la famille ?' === $field->getQuestion()) {
                continue;
            }
            $field->clone($form);
            if (0 === $index) {
                $form1->getFields()[0]->clone($form)->setQuestion('Choix de la seconde pratique instrumentale ou vocale');
            }
        }

        $manager->flush();
    }

    protected function createFormChildOthers(ObjectManager $manager): void
    {
        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_enfant', OrderForm::class);
        $form = $form1->clone();
        $form
            ->setTitle('Autre pratique enfant (moins de 18 ans)')
            ->setDescription('')
            ->setOrderMainLineLabel('Autre pratique enfant')
            ->setOrderMainLineAmount(215)
            ->setEnabled(true)
        ;

        $form->getFields()[0]->getChoices()->clear();
        $form->getFields()[0]->setQuestion('Choix de la pratique');
        foreach (ActivityFixtures::COLLECTIVES as $collective) {
            (new OrderFormFieldChoice($form->getFields()[0]))->setActivity($this->getReference($collective, Activity::class));
        }

        $form->getFields()->remove(1);
        $form->getFields()->remove(2);
        $manager->persist($form);
        $manager->flush();
    }

    protected function createFormAdultFullCourse(ObjectManager $manager): void
    {
        $form1 = $this->getReference('cursus_complet_enfant', OrderForm::class);
        $form = $form1->clone();
        $form
            ->setTitle('Cursus complet adulte')
            ->setDescription('Le cursus complet comprend : '.\PHP_EOL.'- La pratique instrumentale ou vocale'.\PHP_EOL.
                '- La formation musicale (facultative)'.\PHP_EOL.'- La pratique collective (facultative)')
            ->setOrderMainLineLabel('Cursus complet adulte')
            ->setOrderMainLineAmount(695)
            ->setEnabled(true)
        ;

        $form->getFields()[1]->setRequired(false);
        $field = $form->getFields()->get(5)
            ->setRequired(false)
            ->setQuestion('Êtes-vous concerné par le tarif réduit (étudiant, demandeur d\'emploi... ?)');
        $field->getChoices()->clear();
        (new OrderFormFieldChoice($field))->setAllowanceLabel('Tarif réduit')->setAllowancePercentage(14);
        $form->getFields()->remove(3);
        $form->getFields()->remove(6);

        $this->addReference('cursus_complet_adulte', $form);
        $manager->persist($form);

        $manager->flush();
    }

    protected function createFormAdultDoubleCourses(ObjectManager $manager): void
    {
        $form = new OrderForm();
        $form
            ->setTitle('Double cursus adulte')
            ->setDescription('2 disciplines pour le même adhérent')
            ->setOrderMainLineLabel('Double cursus adulte')
            ->setOrderMainLineAmount(1313)
            ->setEnabled(true)
        ;

        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_adulte', OrderForm::class);
        foreach ($form1->getFields() as $index => $field) {
            if ('Est-ce le premier membre inscrit de la famille ?' === $field->getQuestion()) {
                continue;
            }
            $field->clone($form);
            if (0 === $index) {
                $form1->getFields()[0]->clone($form)->setQuestion('Choix de la seconde pratique instrumentale ou vocale');
            }
        }

        $this->addReference('double_cursus_adulte', $form);
        $manager->persist($form);
        $manager->flush();
    }

    protected function createFormAdultOthers(ObjectManager $manager): void
    {
        /** @var OrderForm $form1 */
        $form1 = $this->getReference('cursus_complet_adulte', OrderForm::class);
        $form = $form1->clone();
        $form
            ->setTitle('Autre pratique adulte')
            ->setDescription('')
            ->setOrderMainLineLabel('Autre pratique adulte')
            ->setOrderMainLineAmount(188)
            ->setEnabled(true)
        ;

        $form->getFields()[0]->getChoices()->clear();
        $form->getFields()[0]->setQuestion('Choix de la pratique');
        foreach (ActivityFixtures::COLLECTIVES as $collective) {
            (new OrderFormFieldChoice($form->getFields()[0]))->setActivity($this->getReference($collective, Activity::class));
        }
        $form->getFields()->remove(1);
        $form->getFields()->remove(2);

        $this->addReference('autre_pratique_adulte', $form);
        $manager->persist($form);
        $manager->flush();
    }

    protected function createFormAdultChoir(ObjectManager $manager): void
    {
        /** @var OrderForm $form1 */
        $form1 = $this->getReference('autre_pratique_adulte', OrderForm::class);
        $form = $form1->clone();
        $form
            ->setTitle('Chorale adulte')
            ->setDescription('')
            ->setOrderMainLineLabel('Chorale adulte')
            ->setOrderMainLineAmount(138)
            ->setEnabled(true)
        ;
        $form->getFields()[0]->getChoices()->clear();
        foreach (ActivityFixtures::CHOIR as $choir) {
            (new OrderFormFieldChoice($form->getFields()[0]))->setActivity($this->getReference($choir, Activity::class));
        }

        $manager->persist($form);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ActivityFixtures::class];
    }
}
