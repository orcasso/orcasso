<?php

namespace App\Tests\Controller\Admin;

use App\Entity\LegalRepresentative;
use App\Entity\Member;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class LegalRepresentativeControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_legal_representative_create', ['member' => $member->getId()]));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureLegalRepresentative()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureLegalRepresentative()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_MEMBER_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_legal_representative_create', ['member' => $member->getId()]));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureLegalRepresentative()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureLegalRepresentative()));
    }

    public function testEdit()
    {
        $representative = $this->getFixtureLegalRepresentative();
        $editUrl = $this->getEditUrl($representative);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'legal_representative[firstName]' => $newName = 'New name',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $representative->getMember()->getId()])));
        $this->assertHasFlash('success', 'success.legal_representative.updated');
        $this->assertEquals($newName, $this->getFixtureLegalRepresentative()->getFirstName());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $representativeCount = $member->getLegalRepresentatives()->count();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_legal_representative_create', ['member' => $member->getId()]));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'legal_representative[firstName]' => $newName = 'Lorem',
            'legal_representative[lastName]' => 'Ipsum',
            'legal_representative[phoneNumber]' => '0606060606',
            'legal_representative[email]' => 'email@domain.net',
        ]);

        $this->assertEquals($representativeCount + 1, $this->getDoctrine()->getRepository(LegalRepresentative::class)->count(['member' => $member->getId()]));
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $member->getId()])));
        $this->assertHasFlash('success', 'success.legal_representative.created');
    }

    public function testDelete()
    {
        $representative = $this->getFixtureLegalRepresentative();
        $representativeCount = $this->em->getRepository(LegalRepresentative::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($representative));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($representativeCount - 1, $this->getDoctrine()->getRepository(LegalRepresentative::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $representative->getMember()->getId()])));
        $this->assertHasFlash('success', 'success.legal_representative.deleted');
    }

    protected function getFixtureLegalRepresentative(?int $id = null): LegalRepresentative
    {
        return $this->getDoctrine()->getRepository(LegalRepresentative::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(LegalRepresentative $representative): string
    {
        return $this->getUrl('admin_legal_representative_edit', ['representative' => $representative->getId()]);
    }

    protected function getDeleteUrl(LegalRepresentative $representative): string
    {
        return $this->getUrl('admin_legal_representative_delete', ['representative' => $representative->getId()]);
    }
}
