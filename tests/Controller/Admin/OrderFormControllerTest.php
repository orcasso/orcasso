<?php

namespace App\Tests\Controller\Admin;

use App\Entity\OrderForm;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderFormControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_order_form_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_order_form_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_order_form_create'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureOrderForm()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditHeaderUrl($this->getFixtureOrderForm()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureOrderForm()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_ORDER_FORM_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_order_form_list'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_order_form_list_ajax'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_order_form_create'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureOrderForm()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditHeaderUrl($this->getFixtureOrderForm()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureOrderForm()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_order_form_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.order_form.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_order_form_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals($this->em->getRepository(OrderForm::class)->count(), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $orderForm = $this->getFixtureOrderForm();
        $editUrl = $this->getEditUrl($orderForm);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();
        $this->assertCount($orderForm->getFields()->count(), $this->client->getCrawler()->filter('#order-form-fields > table > tbody tr .btn.btn-primary'));
    }

    public function testEditHeader()
    {
        $orderForm = $this->getFixtureOrderForm();
        $editUrl = $this->getEditHeaderUrl($orderForm);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order_form[description]' => $newDescription = 'Dieu ! la voix sépulcrale'.\PHP_EOL.'Des Djinns !... Quel bruit ils font !',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($orderForm)));
        $this->assertHasFlash('success', 'success.order_form.updated');

        $orderForm = $this->getFixtureOrderForm($orderForm->getId());
        $this->assertEquals($newDescription, $orderForm->getDescription());
    }

    public function testCreate()
    {
        $orderCount = $this->em->getRepository(OrderForm::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_order_form_create'));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order_form[title]' => $newTitle = 'Lorem ipsum',
        ]);

        $this->assertEquals($orderCount + 1, $this->getDoctrine()->getRepository(OrderForm::class)->count());
        $order = $this->em->getRepository(OrderForm::class)->findOneBy(['title' => $newTitle]);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($order)));
        $this->assertHasFlash('success', 'success.order_form.created');
    }

    public function testDelete()
    {
        $orderCount = $this->em->getRepository(OrderForm::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureOrderForm()));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($orderCount - 1, $this->getDoctrine()->getRepository(OrderForm::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_form_list')));
        $this->assertHasFlash('success', 'success.order_form.deleted');
    }

    protected function getFixtureOrderForm(?int $id = null): OrderForm
    {
        return $this->getDoctrine()->getRepository(OrderForm::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(OrderForm $order): string
    {
        return $this->getUrl('admin_order_form_edit', ['orderForm' => $order->getId()]);
    }

    protected function getEditHeaderUrl(OrderForm $order): string
    {
        return $this->getUrl('admin_order_form_edit_header', ['orderForm' => $order->getId()]);
    }

    protected function getDeleteUrl(OrderForm $order): string
    {
        return $this->getUrl('admin_order_form_delete', ['orderForm' => $order->getId()]);
    }
}
