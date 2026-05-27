<?php

namespace App\Tests\Controller\Admin;

use App\Entity\FiscalPeriod;
use App\Entity\Member;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\FiscalPeriodRepository;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class FiscalPeriodControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getNonCurrentPeriod()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getNonCurrentPeriod()));
        $this->assertRedirectToLogin(Request::METHOD_POST, $this->getSetCurrentUrl($this->getNonCurrentPeriod()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_FISCAL_PERIOD_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list_ajax'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getNonCurrentPeriod()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getNonCurrentPeriod()));
        $this->assertAccessDenied(Request::METHOD_POST, $this->getSetCurrentUrl($this->getNonCurrentPeriod()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.fiscal_period.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_fiscal_period_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals($this->em->getRepository(FiscalPeriod::class)->count(), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $period = $this->getNonCurrentPeriod();
        $editUrl = $this->getEditUrl($period);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'fiscal_period[name]' => $newName = 'Période modifiée',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($editUrl));
        $this->assertHasFlash('success', 'success.fiscal_period.updated');

        $period = $this->em->getRepository(FiscalPeriod::class)->find($period->getId());
        $this->assertEquals($newName, $period->getName());
    }

    public function testCreate()
    {
        $periodCount = $this->em->getRepository(FiscalPeriod::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'fiscal_period[name]' => $name = 'Nouvelle période',
        ]);

        $this->assertEquals($periodCount + 1, $this->getDoctrine()->getRepository(FiscalPeriod::class)->count());
        $period = $this->em->getRepository(FiscalPeriod::class)->findOneBy(['name' => $name]);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($period)));
        $this->assertHasFlash('success', 'success.fiscal_period.created');
        $this->assertFalse($period->isCurrent());
    }

    public function testDelete()
    {
        $period = (new FiscalPeriod())->setName('Période supprimable');
        $this->updateEntity($period);
        $periodCount = $this->em->getRepository(FiscalPeriod::class)->count();

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($period));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($periodCount - 1, $this->getDoctrine()->getRepository(FiscalPeriod::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_fiscal_period_list')));
        $this->assertHasFlash('success', 'success.fiscal_period.deleted');
    }

    public function testNotRemovableWhenCurrent()
    {
        $period = $this->getCurrentPeriod();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($period));

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($period)));
        $this->assertHasFlash('warning', 'warning.fiscal_period.not_removable');
    }

    public function testNotRemovableWhenHasOrders()
    {
        $period = (new FiscalPeriod())->setName('Période avec commande');
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $order = (new Order($period))->setMember($member);
        $this->em->persist($period);
        $this->em->persist($order);
        $this->em->flush();

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($period));

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($period)));
        $this->assertHasFlash('warning', 'warning.fiscal_period.not_removable');
    }

    public function testSetCurrent()
    {
        $previousCurrent = $this->getCurrentPeriod();
        $period = $this->getNonCurrentPeriod();

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getSetCurrentUrl($period));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'));

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($period)));
        $this->assertHasFlash('success', 'success.fiscal_period.set_current');

        $this->assertTrue($this->em->getRepository(FiscalPeriod::class)->find($period->getId())->isCurrent());
        $this->assertFalse($this->em->getRepository(FiscalPeriod::class)->find($previousCurrent->getId())->isCurrent());
    }

    protected function getRepository(): FiscalPeriodRepository
    {
        return $this->getDoctrine()->getRepository(FiscalPeriod::class);
    }

    protected function getCurrentPeriod(): FiscalPeriod
    {
        return $this->getRepository()->getCurrentOrFail();
    }

    protected function getNonCurrentPeriod(): FiscalPeriod
    {
        return $this->getRepository()->findOneBy(['current' => false]);
    }

    protected function getEditUrl(FiscalPeriod $period): string
    {
        return $this->getUrl('admin_fiscal_period_edit', ['fiscalPeriod' => $period->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('admin_fiscal_period_create');
    }

    protected function getDeleteUrl(FiscalPeriod $period): string
    {
        return $this->getUrl('admin_fiscal_period_delete', ['fiscalPeriod' => $period->getId()]);
    }

    protected function getSetCurrentUrl(FiscalPeriod $period): string
    {
        return $this->getUrl('admin_fiscal_period_set_current', ['fiscalPeriod' => $period->getId()]);
    }
}
