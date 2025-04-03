<?php

namespace App\Tests\Controller\Admin;

use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class DashboardControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_dashboard'));
    }

    public function testDashboard()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_dashboard'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('home.index.title', [], null, 'h4');
    }
}
