<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use App\Entity\Payment;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class PaymentControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_payment_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_payment_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixturePayment()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditHeaderUrl($this->getFixturePayment()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixturePayment()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_payment_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.payment.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_payment_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals($this->em->getRepository(Payment::class)->count(), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $payment = $this->getFixturePayment();
        $editUrl = $this->getEditUrl($payment);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();
        $this->assertCount($payment->getOrders()->count(), $this->client->getCrawler()->filter('#payment-lines > table > tbody tr'));
    }

    public function testEditHeader()
    {
        $payment = $this->getFixturePayment();
        $editUrl = $this->getEditHeaderUrl($payment);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'payment[notes]' => $newNotes = 'Dieu ! la voix sépulcrale'.\PHP_EOL.'Des Djinns !... Quel bruit ils font !',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($payment)));
        $this->assertHasFlash('success', 'success.payment.updated');

        $payment = $this->getFixturePayment($payment->getId());
        $this->assertEquals($newNotes, $payment->getNotes());
    }

    public function testCreate()
    {
        $paymentCount = $this->em->getRepository(Payment::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'payment[notes]' => $newNotes = 'Mer grise'.\PHP_EOL.'Où brise'.\PHP_EOL.'La brise,'.\PHP_EOL.'Tout dort.',
        ]);

        $this->assertEquals($paymentCount + 1, $this->getDoctrine()->getRepository(Payment::class)->count());
        $payment = $this->em->getRepository(Payment::class)->findOneBy(['notes' => $newNotes]);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($payment)));
        $this->assertHasFlash('success', 'success.payment.created');
    }

    public function testDelete()
    {
        $this->em->persist($payment = (new Payment())->setMember($this->em->getRepository(Member::class)->findOneBy([])));
        $this->em->flush();
        $paymentCount = $this->em->getRepository(Payment::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($payment));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($paymentCount - 1, $this->getDoctrine()->getRepository(Payment::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_payment_list')));
        $this->assertHasFlash('success', 'success.payment.deleted');
    }

    protected function getFixturePayment(?int $id = null): Payment
    {
        return $this->getDoctrine()->getRepository(Payment::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(Payment $payment): string
    {
        return $this->getUrl('admin_payment_edit', ['payment' => $payment->getId()]);
    }

    protected function getEditHeaderUrl(Payment $payment): string
    {
        return $this->getUrl('admin_payment_edit_header', ['payment' => $payment->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('admin_payment_create');
    }

    protected function getDeleteUrl(Payment $payment): string
    {
        return $this->getUrl('admin_payment_delete', ['payment' => $payment->getId()]);
    }
}
