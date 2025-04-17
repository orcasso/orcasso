<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\MemberDocument;
use App\Entity\User;
use App\Form\MemberDocumentType;
use App\Repository\MemberDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member-documents')]
#[IsGranted(User::ROLE_ADMIN_MEMBER_EDIT)]
final class MemberDocumentController extends AbstractController
{
    public function __construct(protected MemberDocumentRepository $repository)
    {
    }

    #[Route('/new/{member}', name: 'admin_member_document_create', methods: ['GET', 'POST'])]
    public function create(Request $request, Member $member): Response
    {
        $document = new MemberDocument($member);
        $form = $this->createForm(MemberDocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->storeFromUploadedFile($document, $form->get('file')->getData());
            $this->repository->update($document);
            $this->addFlash('success', 'success.member_document.created');

            return $this->redirectToRoute('admin_member_show', ['member' => $document->getMember()->getId()]);
        }

        return $this->render('admin/member_document/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{document}', name: 'admin_member_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MemberDocument $document): Response
    {
        $form = $this->createForm(MemberDocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($document);
            $this->addFlash('success', 'success.member_document.updated');

            return $this->redirectToRoute('admin_member_show', ['member' => $document->getMember()->getId()]);
        }

        return $this->render('admin/member_document/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{document}/download', name: 'admin_member_document_download', methods: ['GET'])]
    public function download(MemberDocument $document): Response
    {
        $response = new Response($this->repository->getContent($document));
        $response->headers->set('Content-Type', $document->getMimeType());
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$document->getFileName());

        return $response;
    }

    #[Route('/{document}/delete', name: 'admin_member_document_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, MemberDocument $document): Response
    {
        if (!$this->repository->isRemovable($document)) {
            $this->addFlash('warning', 'warning.member_document.not_removable');

            return $this->redirectToRoute('admin_member_document_edit', ['document' => $document->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($document);
            $this->addFlash('success', 'success.member_document.deleted');

            return $this->redirectToRoute('admin_member_show', ['member' => $document->getMember()->getId()]);
        }

        return $this->render('admin/member_document/delete.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }
}
