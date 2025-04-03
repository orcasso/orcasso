<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;

final class HomeControllerTest extends AbstractWebTestCase
{
    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, $this->getUrl('homepage'));
        $this->assertResponseIsSuccessful();
    }
}
