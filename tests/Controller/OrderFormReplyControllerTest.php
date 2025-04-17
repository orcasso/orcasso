<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\OrderFormReply;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class OrderFormReplyControllerTest extends AbstractWebTestCase
{
    public function testReply()
    {
        $faker = Factory::create('fr_FR');
        $replyCount = $this->em->getRepository(OrderFormReply::class)->count();
        $orderCount = $this->em->getRepository(Order::class)->count(['status' => Order::STATUS_PENDING]);

        foreach ($this->getDoctrine()->getRepository(OrderForm::class)->findAll() as $orderForm) {
            $this->client->request(Request::METHOD_GET, $this->getUrl('order_form_reply', ['orderForm' => $orderForm->getId()]));
            $buttonCrawlerNode = $this->client->getCrawler()->selectButton($this->trans('_meta.word.save'));
            $form = $buttonCrawlerNode->form();
            $form['order_form_reply[memberData][firstName]'] = $faker->firstName();
            $form['order_form_reply[memberData][lastName]'] = $faker->lastName();
            $form['order_form_reply[memberData][birthDate]'] = $faker->date('Y-m-d', 'yesterday');
            $form['order_form_reply[memberData][email]'] = $faker->email();
            $form['order_form_reply[memberData][phoneNumber]'] = $faker->phoneNumber();
            $form['order_form_reply[memberData][street1]'] = $faker->streetAddress();
            $form['order_form_reply[memberData][postalCode]'] = $faker->postcode();
            $form['order_form_reply[memberData][city]'] = $faker->city();
            foreach ($orderForm->getFields() as $field) {
                if (!$field->isRequired() && random_int(0, 1)) {
                    continue;
                }
                if (OrderFormField::TYPE_DOCUMENT === $field->getType()) {
                    $form["order_form_reply[fieldValues_{$field->getPosition()}]"] =
                        new UploadedFile(__DIR__.'/../../src/Dev/DataFixtures/data/quotient_familial.jpeg', 'quotient_familial.jpeg')
                    ;
                    continue;
                }
                /** @var OrderFormFieldChoice $choice */
                $choice = $faker->randomElement($field->getChoices());
                $form["order_form_reply[fieldValues_{$field->getPosition()}]"]->select($choice->getActivity()?->getName() ?? $choice->getAllowanceLabel());
            }
            $this->client->submit($form);
            $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('homepage')));
            $this->assertHasFlash('success', 'success.order.created');
            $this->assertEquals(++$replyCount, $this->em->getRepository(OrderFormReply::class)->count());
            $this->assertEquals(++$orderCount, $this->em->getRepository(Order::class)->count(['status' => Order::STATUS_PENDING]));
        }
    }
}
