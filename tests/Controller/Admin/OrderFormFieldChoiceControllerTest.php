<?php

namespace App\Tests\Controller\Admin;

use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderFormFieldChoiceControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $field = $this->getFixtureOrderFormField();
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($field->getChoices()->first()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl($field));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($field->getChoices()->first()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_ORDER_FORM_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $field = $this->getFixtureOrderFormField();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($field->getChoices()->first()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl($field));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($field->getChoices()->first()));
    }

    public function testEdit()
    {
        $choice = $this->getFixtureOrderFormField()->getChoices()->first();
        $editUrl = $this->getEditUrl($choice);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), $choice->getActivity() ?
            ['order_form_field_choice[activityAmount]' => 55.5] : ['order_form_field_choice[allowancePercentage]' => 55.5]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_field_edit', ['field' => $choice->getField()->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.updated');

        /** @var OrderFormFieldChoice $choice */
        $choice = $this->getFixtureOrderFormField()->getChoices()->first();
        $this->assertEquals(55.5, $choice->getActivityAmount() ?: $choice->getAllowancePercentage());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $type = random_int(0, 1) ? OrderFormField::TYPE_ALLOWANCE_CHOICE : OrderFormField::TYPE_ACTIVITY_CHOICE;
        $field = (new OrderFormField($this->getFixtureOrderFormField()->getForm()))->setType($type);
        $this->updateEntity($field);
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl($field));
        $this->assertResponseIsSuccessful();
        $this->client->followRedirects(false);

        $buttonCrawlerNode = $this->client->getCrawler()->selectButton($this->trans('_meta.word.save'));
        $form = $buttonCrawlerNode->form();
        if (OrderFormField::TYPE_ACTIVITY_CHOICE === $field->getType()) {
            $form['order_form_field_choice[activity]']->select($form['order_form_field_choice[activity]']->availableOptionValues()[1]);
            $form['order_form_field_choice[activityAmount]'] = 5;
        } else {
            $form['order_form_field_choice[allowanceLabel]'] = 'test';
            $form['order_form_field_choice[allowancePercentage]'] = 5;
        }
        $this->client->submit($form);
        $this->assertEquals(1, $this->getFixtureOrderFormField($field->getId())->getChoices()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_field_edit', ['field' => $field->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.updated');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $field = $this->getFixtureOrderFormField();
        $choice = $field->getChoices()->first();
        $count = $field->getChoices()->count();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($choice));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($count - 1, $this->getFixtureOrderFormField()->getChoices()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_field_edit', ['field' => $field->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.updated');
    }

    protected function getFixtureOrderFormField(?int $id = null): OrderFormField
    {
        return $this->getDoctrine()->getRepository(OrderFormField::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(OrderFormFieldChoice $choice): string
    {
        return $this->getUrl('admin_order_form_field_choice_edit', ['choice' => $choice->getId()]);
    }

    protected function getCreateUrl(OrderFormField $field): string
    {
        return $this->getUrl('admin_order_form_field_choice_create', ['field' => $field->getId()]);
    }

    protected function getDeleteUrl(OrderFormFieldChoice $choice): string
    {
        return $this->getUrl('admin_order_form_field_choice_delete', ['choice' => $choice->getId()]);
    }
}
