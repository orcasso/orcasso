<?php

namespace App\Tests\Controller;

use App\Dev\DataFixtures\ActivityFixtures;
use App\Entity\Activity;
use Symfony\Component\HttpFoundation\Request;

final class ActivityControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('activity_list'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('activity_list_ajax'));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureActivity()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureActivity()));
    }

    public function testList()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('activity_list'));
        $this->assertResponseIsSuccessful();
        $this->assertHasHtmlTitle('activity.list.title', [], null, 'h4');
    }

    public function testListAjax()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getUrl('activity_list_ajax'));
        $this->assertResponseIsSuccessful();
        $this->assertEquals(\count(ActivityFixtures::activities()), $this->getResponseJsonContent()['totalRows']);
    }

    public function testEdit()
    {
        $activity = $this->getFixtureActivity();
        $editUrl = $this->getEditUrl($activity);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'activity[name]' => $newName = 'Activity edit',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($editUrl));
        $this->assertHasFlash('success', 'success.activity.updated');

        $activity = $this->getFixtureActivity($newName);
        $this->assertEquals($newName, $activity->getName());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getCreateUrl());
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'activity[name]' => $name = 'New activity',
        ]);

        $this->assertEquals(\count(ActivityFixtures::activities()) + 1, $this->getDoctrine()->getRepository(Activity::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getEditUrl($activity = $this->getFixtureActivity($name))));
        $this->assertHasFlash('success', 'success.activity.created');
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureActivity()));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals(\count(ActivityFixtures::activities()) - 1, $this->getDoctrine()->getRepository(Activity::class)->count());

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('activity_list')));
        $this->assertHasFlash('success', 'success.activity.deleted');
    }

    protected function getFixtureActivity(?string $name = null): Activity
    {
        $name = $name ?? ActivityFixtures::getCompleteName(ActivityFixtures::activities()[1]);

        return $this->getDoctrine()->getRepository(Activity::class)->findOneBy(['name' => $name]);
    }

    protected function getEditUrl(Activity $activity): string
    {
        return $this->getUrl('activity_edit', ['activity' => $activity->getId()]);
    }

    protected function getCreateUrl(): string
    {
        return $this->getUrl('activity_create');
    }

    protected function getDeleteUrl(Activity $activity): string
    {
        return $this->getUrl('activity_delete', ['activity' => $activity->getId()]);
    }
}
