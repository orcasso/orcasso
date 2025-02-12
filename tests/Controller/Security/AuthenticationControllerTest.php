<?php

namespace App\Tests\Controller\Security;

use App\Dev\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginWithInvalidUser(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm($this->trans('security.authentication.login.submit'), [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', $this->trans('Invalid credentials.', [], 'security'));
    }

    public function testLoginWithInvalidPassword()
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm($this->trans('security.authentication.login.submit'), [
            '_username' => UserFixtures::USERS[0],
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', $this->trans('Invalid credentials.', [], 'security'));
    }

    public function testLogin()
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        // Success - Login with valid credentials is allowed.
        $this->client->submitForm($this->trans('security.authentication.login.submit'), [
            '_username' => UserFixtures::USERS[0],
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }

    protected function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
    }
}
