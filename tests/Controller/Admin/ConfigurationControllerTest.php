<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Configuration;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class ConfigurationControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_configuration_edit'));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_CONFIGURATION_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_configuration_edit'));
    }

    public function testEdit()
    {
        $editUrl = $this->getUrl('admin_configuration_edit');
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'configurations[payment_method_cheque_instruction][value]' => $newInstruction = 'Lorem ipsum',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($editUrl));
        $this->assertHasFlash('success', 'success.configuration.updated');

        $this->assertEquals(
            $newInstruction,
            $this->getDoctrine()->getRepository(Configuration::class)->getValue(Configuration::ITEM_PAYMENT_METHOD_CHEQUE_INSTRUCTION)
        );
    }
}
