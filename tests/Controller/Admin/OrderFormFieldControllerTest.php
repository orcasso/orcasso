<?php

namespace App\Tests\Controller\Admin;

use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderFormFieldControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $orderForm = $this->getFixtureOrderForm();
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($orderForm->getFields()->first()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl($orderForm));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($orderForm->getFields()->first()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_ORDER_FORM_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $orderForm = $this->getFixtureOrderForm();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($orderForm->getFields()->first()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl($orderForm));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($orderForm->getFields()->first()));
    }

    public function testEdit()
    {
        $field = $this->getFixtureOrderForm()->getFields()->first();
        $editUrl = $this->getEditUrl($field);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order_form_field[question]' => $newQuestion = 'Lorem ipsum',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_field_edit', ['field' => $field->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.updated');

        $field = $this->getFixtureOrderForm()->getFields()->first();
        $this->assertEquals($newQuestion, $field->getQuestion());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $orderForm = $this->getFixtureOrderForm();
        $fieldCount = $orderForm->getFields()->count();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl($orderForm));
        $this->assertResponseIsSuccessful();
        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), ['order_form_field[question]' => 'Test']);
        ++$fieldCount;
        $this->assertEquals($fieldCount, $this->getFixtureOrderForm()->getFields()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_field_edit', ['field' => $this->getFixtureOrderForm()->getFields()->last()->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.created');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $orderForm = $this->getFixtureOrderForm();
        $field = $orderForm->getFields()->first();
        $count = $orderForm->getFields()->count();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($field));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($count - 1, $this->getFixtureOrderForm()->getFields()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_edit', ['orderForm' => $orderForm->getId()])));
        $this->assertHasFlash('success', 'success.order_form_field.deleted');
    }

    protected function getFixtureOrderForm(?int $id = null): OrderForm
    {
        return $this->getDoctrine()->getRepository(OrderForm::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(OrderFormField $field): string
    {
        return $this->getUrl('admin_order_form_field_edit', ['field' => $field->getId()]);
    }

    protected function getCreateUrl(OrderForm $orderForm): string
    {
        return $this->getUrl('admin_order_form_field_create', ['orderForm' => $orderForm->getId()]);
    }

    protected function getDeleteUrl(OrderFormField $field): string
    {
        return $this->getUrl('admin_order_form_field_delete', ['field' => $field->getId()]);
    }
}
