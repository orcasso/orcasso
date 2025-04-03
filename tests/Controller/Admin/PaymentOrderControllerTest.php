<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class PaymentOrderControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $payment = $this->getFixturePayment();
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($payment->getOrders()->first()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl($payment));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($payment->getOrders()->first()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_PAYMENT_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $payment = $this->getFixturePayment();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($payment->getOrders()->first()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl($payment));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($payment->getOrders()->first()));
    }

    public function testEdit()
    {
        $order = $this->getFixturePayment()->getOrders()->first();
        $editUrl = $this->getEditUrl($order);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'payment_order[amount]' => $newAmount = 10,
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_payment_edit', ['payment' => $order->getPayment()->getId()])));
        $this->assertHasFlash('success', 'success.payment.updated');

        $order = $this->getFixturePayment()->getOrders()->first();
        $this->assertEquals($newAmount, $order->getAmount());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $payment = $this->getFixturePayment();
        $orderCount = $payment->getOrders()->count();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl($payment));
        $this->assertResponseIsSuccessful();
        $this->client->followRedirects(false);
        $fieldValues = ['payment_order[amount]' => 5];
        $this->client->submitForm($this->trans('_meta.word.save'), $fieldValues);
        ++$orderCount;
        $this->assertEquals($orderCount, $this->getFixturePayment()->getOrders()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_payment_edit', ['payment' => $payment->getId()])));
        $this->assertHasFlash('success', 'success.payment.updated');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $payment = $this->getFixturePayment();
        $order = $payment->getOrders()->first();
        $count = $payment->getOrders()->count();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($order));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($count - 1, $this->getFixturePayment()->getOrders()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_payment_edit', ['payment' => $payment->getId()])));
        $this->assertHasFlash('success', 'success.payment.updated');
    }

    protected function getFixturePayment(?int $id = null): Payment
    {
        return $this->getDoctrine()->getRepository(Payment::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(PaymentOrder $order): string
    {
        return $this->getUrl('admin_payment_order_edit', ['payment' => $order->getPayment()->getId(), 'paymentOrder' => $order->getId()]);
    }

    protected function getCreateUrl(Payment $payment): string
    {
        return $this->getUrl('admin_payment_order_create', ['payment' => $payment->getId()]);
    }

    protected function getDeleteUrl(PaymentOrder $order): string
    {
        return $this->getUrl('admin_payment_order_delete', ['payment' => $order->getPayment()->getId(), 'paymentOrder' => $order->getId()]);
    }
}
