<?php

namespace App\Tests\Controller\Admin;

use App\Dev\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class UserControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_user_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_user_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureUser()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureUser()));
    }

    public function testRoleNotGranted()
    {
        $this->authenticateUser(UserFixtures::USERS[1]);
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_user_list'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_user_list_ajax'));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureUser()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureUser()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_user_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('admin.user.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_user_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals(\count(UserFixtures::USERS), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $user = $this->getFixtureUser();
        $editUrl = $this->getEditUrl($user);
        $newName = 'User edit';

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'user[name]' => $newName,
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($editUrl));
        $this->assertHasFlash('success', 'success.user.updated');

        $user = $this->getFixtureUser($user->getEmail());
        $this->assertEquals($newName, $user->getName());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'user[name]' => 'New user',
            'user[password][first]' => 'Password123456$$',
            'user[password][second]' => 'Password123456$$',
            'user[email]' => $email = 'new.user@domain.net',
        ]);

        $this->assertEquals(\count(UserFixtures::USERS) + 1, $this->getDoctrine()->getRepository(User::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($user = $this->getFixtureUser($email))));
        $this->assertHasFlash('success', 'success.user.created');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureUser()));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals(\count(UserFixtures::USERS) - 1, $this->getDoctrine()->getRepository(User::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_user_list')));
        $this->assertHasFlash('success', 'success.user.deleted');
    }

    public function testNotRemovable()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureUser(UserFixtures::USERS[0])));

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($this->getFixtureUser(UserFixtures::USERS[0]))));
        $this->assertHasFlash('warning', 'warning.user.not_removable');
    }

    protected function getFixtureUser(string $email = UserFixtures::USERS[1]): User
    {
        return $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    protected function getEditUrl(User $user): string
    {
        return $this->getUrl('admin_user_edit', ['user' => $user->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('admin_user_create');
    }

    protected function getDeleteUrl(User $user): string
    {
        return $this->getUrl('admin_user_delete', ['user' => $user->getId()]);
    }
}
