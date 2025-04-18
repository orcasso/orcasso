<?php

namespace App\Controller\Admin;

use App\Entity\LegalRepresentative;
use App\Entity\Member;
use App\Entity\User;
use App\Form\LegalRepresentativeType;
use App\Repository\LegalRepresentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member-representatives')]
#[IsGranted(User::ROLE_ADMIN_MEMBER_EDIT)]
final class LegalRepresentativeController extends AbstractController
{
    public function __construct(protected LegalRepresentativeRepository $repository)
    {
    }

    #[Route('/new/{member}', name: 'admin_legal_representative_create', methods: ['GET', 'POST'])]
    public function create(Request $request, Member $member): Response
    {
        $representative = new LegalRepresentative($member);
        $form = $this->createForm(LegalRepresentativeType::class, $representative);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($representative);
            $this->addFlash('success', 'success.legal_representative.created');

            return $this->redirectToRoute('admin_member_show', ['member' => $representative->getMember()->getId()]);
        }

        return $this->render('admin/legal_representative/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{representative}', name: 'admin_legal_representative_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LegalRepresentative $representative): Response
    {
        $form = $this->createForm(LegalRepresentativeType::class, $representative);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($representative);
            $this->addFlash('success', 'success.legal_representative.updated');

            return $this->redirectToRoute('admin_member_show', ['member' => $representative->getMember()->getId()]);
        }

        return $this->render('admin/legal_representative/edit.html.twig', [
            'representative' => $representative,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{representative}/delete', name: 'admin_legal_representative_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, LegalRepresentative $representative): Response
    {
        if (!$this->repository->isRemovable($representative)) {
            $this->addFlash('warning', 'warning.legal_representative.not_removable');

            return $this->redirectToRoute('admin_legal_representative_edit', ['representative' => $representative->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($representative);
            $this->addFlash('success', 'success.legal_representative.deleted');

            return $this->redirectToRoute('admin_member_show', ['member' => $representative->getMember()->getId()]);
        }

        return $this->render('admin/legal_representative/delete.html.twig', [
            'representative' => $representative,
            'form' => $form->createView(),
        ]);
    }
}
