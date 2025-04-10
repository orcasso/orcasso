<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_order_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_order_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureOrder()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditHeaderUrl($this->getFixtureOrder()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getChangeStatusUrl($this->getFixtureOrder()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureOrder()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_ORDER_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_order_list'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_order_list_ajax'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureOrder()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditHeaderUrl($this->getFixtureOrder()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getChangeStatusUrl($this->getFixtureOrder()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureOrder()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_order_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.order.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_order_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals($this->em->getRepository(Order::class)->count(), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $order = $this->getFixtureOrder();
        $editUrl = $this->getEditUrl($order);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();
        $this->assertCount($order->getLines()->count(), $this->client->getCrawler()->filter('#order-lines > table > tbody tr'));
    }

    public function testEditHeader()
    {
        $order = $this->getFixtureOrder();
        $editUrl = $this->getEditHeaderUrl($order);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order[notes]' => $newNotes = 'Dieu ! la voix sépulcrale'.\PHP_EOL.'Des Djinns !... Quel bruit ils font !',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($order)));
        $this->assertHasFlash('success', 'success.order.updated');

        $order = $this->getFixtureOrder($order->getId());
        $this->assertEquals($newNotes, $order->getNotes());
    }

    public function testChangeStatus()
    {
        $order = $this->getFixtureOrder();
        $order->setStatus(Order::STATUS_PENDING);
        $this->updateEntity($order);

        $url = $this->getChangeStatusUrl($order, $newStatus = Order::STATUS_VALIDATED);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'));

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($order)));
        $this->assertHasFlash('success', 'success.order.updated');

        $order = $this->getFixtureOrder($order->getId());
        $this->assertEquals($newStatus, $order->getStatus());
    }

    public function testCreate()
    {
        $orderCount = $this->em->getRepository(Order::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order[notes]' => $newNotes = 'Mer grise'.\PHP_EOL.'Où brise'.\PHP_EOL.'La brise,'.\PHP_EOL.'Tout dort.',
        ]);

        $this->assertEquals($orderCount + 1, $this->getDoctrine()->getRepository(Order::class)->count());
        $order = $this->em->getRepository(Order::class)->findOneBy(['notes' => $newNotes]);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($order)));
        $this->assertHasFlash('success', 'success.order.created');
    }

    public function testDelete()
    {
        $orderCount = $this->em->getRepository(Order::class)->count();
        $this->authenticateUser();

        $order = $this->getFixtureOrder();
        foreach ($order->getPayments() as $payment) {
            $order->getPayments()->removeElement($payment);
            $this->em->remove($payment);
            $this->em->flush();
        }
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($order));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($orderCount - 1, $this->getDoctrine()->getRepository(Order::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_list')));
        $this->assertHasFlash('success', 'success.order.deleted');
    }

    protected function getFixtureOrder(?int $id = null): Order
    {
        return $this->getDoctrine()->getRepository(Order::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(Order $order): string
    {
        return $this->getUrl('admin_order_edit', ['order' => $order->getId()]);
    }

    protected function getEditHeaderUrl(Order $order): string
    {
        return $this->getUrl('admin_order_edit_header', ['order' => $order->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('admin_order_create');
    }

    protected function getDeleteUrl(Order $order): string
    {
        return $this->getUrl('admin_order_delete', ['order' => $order->getId()]);
    }

    protected function getChangeStatusUrl(Order $order, string $status = Order::STATUS_VALIDATED): string
    {
        return $this->getUrl('admin_order_change_status', ['order' => $order->getId(), 'status' => $status]);
    }
}
