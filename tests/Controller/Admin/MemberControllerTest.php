<?php

namespace App\Tests\Controller\Admin;

use App\Dev\DataFixtures\MemberFixtures;
use App\Dev\DataFixtures\UserFixtures;
use App\Entity\Member;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class MemberControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_member_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_member_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureMember()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureMember()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_MEMBER_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_member_list'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_member_list_ajax'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureMember()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureMember()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_member_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.member.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_member_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals(MemberFixtures::COUNT, $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $member = $this->getFixtureMember();
        $editUrl = $this->getEditUrl($member);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'member[firstName]' => $newName = 'MemberName',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($editUrl));
        $this->assertHasFlash('success', 'success.member.updated');

        $member = $this->getFixtureMember();
        $this->assertEquals($newName, $member->getFirstName());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'member[firstName]' => $newName = 'MemberName',
            'member[lastName]' => 'LastName',
            'member[birthDate]' => date_create_immutable('last year')->format('Y-m-d'),
            'member[email]' => $email = 'member@lastname.fr',
        ]);

        $this->assertEquals(MemberFixtures::COUNT + 1, $this->getDoctrine()->getRepository(Member::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($member = $this->getFixtureMember($email))));
        $this->assertHasFlash('success', 'success.member.created');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureMember()));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_list')));
        $this->assertHasFlash('success', 'success.member.deleted');
        $this->assertEquals(MemberFixtures::COUNT - 1, $this->getDoctrine()->getRepository(Member::class)->count());
    }

    protected function getFixtureMember(string $email = UserFixtures::USERS[0]): Member
    {
        return $this->getDoctrine()->getRepository(Member::class)->findOneBy(['email' => $email]);
    }

    protected function getEditUrl(Member $member): string
    {
        return $this->getUrl('admin_member_edit', ['member' => $member->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('admin_member_create');
    }

    protected function getDeleteUrl(Member $member): string
    {
        return $this->getUrl('admin_member_delete', ['member' => $member->getId()]);
    }
}
