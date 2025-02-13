<?php

namespace App\Tests\Controller;

use App\Dev\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractWebTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function authenticateUser(string $email = UserFixtures::USERS[0]): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => $email]);
        $this->client->loginUser($testUser);
    }

    protected function assertRedirectToLogin(string $method, string $url): void
    {
        $this->client->request($method, $url);

        static::assertResponseRedirects(
            $this->getContainer()->get('router')->generate('security_authentication_login', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    protected function assertAccessDenied(string $method, string $url): void
    {
        $this->client->request($method, $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    protected function assertHasHtmlTitle(string $title, array $parameters = [], ?string $domain = null, string $level = 'h1'): void
    {
        $transTitle = $this->trans($title, $parameters, $domain);
        $filter = $level.':contains("'.$transTitle.'")';
        $this->assertGreaterThanOrEqual(1, $this->client->getCrawler()->filter($filter)->count());
    }

    protected function assertHasFlash(string $type, string $message): void
    {
        $bag = $this->client->getRequest()->getSession()->getFlashBag()->get($type);
        $this->assertContains($message, $bag);
    }

    protected function getDoctrine(): \Doctrine\Bundle\DoctrineBundle\Registry
    {
        return $this->getContainer()->get('doctrine');
    }

    protected function updateEntity(object $entity): void
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($entity);
        $manager->flush();
    }

    protected function trans(string $id, array $parameters = [], ?string $domain = null): string
    {
        $locale = $this->client->getRequest()->getLocale();

        return $this->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    protected function getResponseContent(): string
    {
        return $this->client->getResponse()->getContent();
    }

    protected function getResponseJsonContent(): array
    {
        return json_decode($this->getResponseContent(), true);
    }

    protected function getUrl(string $route, array $params = [], int $absolute = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->getContainer()->get('router')->generate($route, $params, $absolute);
    }
}
