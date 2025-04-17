<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Member;
use App\Entity\MemberDocument;
use App\Entity\User;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class MemberDocumentControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getUrl('admin_member_document_create', ['member' => $member->getId()]));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($this->getFixtureMemberDocument()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDownloadUrl($this->getFixtureMemberDocument()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureMemberDocument()));
    }

    public function testRoleNotGranted()
    {
        $admin = $this->getUser();
        $admin->setRoles(array_diff(User::ROLES, [User::ROLE_ADMIN_MEMBER_EDIT]));
        $this->updateEntity($admin);
        $this->authenticateUser();
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $this->assertAccessDenied(Request::METHOD_GET, $this->getUrl('admin_member_document_create', ['member' => $member->getId()]));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getEditUrl($this->getFixtureMemberDocument()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDownloadUrl($this->getFixtureMemberDocument()));
        $this->assertAccessDenied(Request::METHOD_GET, $this->getDeleteUrl($this->getFixtureMemberDocument()));
    }

    public function testEdit()
    {
        $document = $this->getFixtureMemberDocument();
        $editUrl = $this->getEditUrl($document);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'member_document[name]' => $newName = 'New name',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $document->getMember()->getId()])));
        $this->assertHasFlash('success', 'success.member_document.updated');
        $this->assertEquals($newName, $this->getFixtureMemberDocument()->getName());
    }

    public function testDownload()
    {
        $document = $this->getFixtureMemberDocument();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDownloadUrl($document));
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'image/jpeg');
    }

    public function testCreate()
    {
        $documentCount = $this->em->getRepository(MemberDocument::class)->count();
        $this->authenticateUser();
        $member = $this->em->getRepository(Member::class)->findOneBy([]);
        $this->client->request(Request::METHOD_GET, $this->getUrl('admin_member_document_create', ['member' => $member->getId()]));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'member_document[file]' => new UploadedFile(__DIR__.'/../../../src/Dev/DataFixtures/data/quotient_familial.jpeg', 'quotient_familial.jpeg'),
            'member_document[name]' => $newName = 'Lorem ipsum',
        ]);

        $this->assertEquals($documentCount + 1, $this->getDoctrine()->getRepository(MemberDocument::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $member->getId()])));
        $this->assertHasFlash('success', 'success.member_document.created');
    }

    public function testDelete()
    {
        $document = $this->getFixtureMemberDocument();
        $documentCount = $this->em->getRepository(MemberDocument::class)->count();
        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($document));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($documentCount - 1, $this->getDoctrine()->getRepository(MemberDocument::class)->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_member_show', ['member' => $document->getMember()->getId()])));
        $this->assertHasFlash('success', 'success.member_document.deleted');
    }

    protected function getFixtureMemberDocument(?int $id = null): MemberDocument
    {
        return $this->getDoctrine()->getRepository(MemberDocument::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(MemberDocument $document): string
    {
        return $this->getUrl('admin_member_document_edit', ['document' => $document->getId()]);
    }

    protected function getDownloadUrl(MemberDocument $document): string
    {
        return $this->getUrl('admin_member_document_download', ['document' => $document->getId()]);
    }

    protected function getDeleteUrl(MemberDocument $document): string
    {
        return $this->getUrl('admin_member_document_delete', ['document' => $document->getId()]);
    }
}
