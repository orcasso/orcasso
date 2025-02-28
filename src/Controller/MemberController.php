<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use App\Table\MemberTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/members')]
final class MemberController extends AbstractController
{
    public function __construct(protected MemberRepository $repository)
    {
    }

    #[Route('/', name: 'member_list')]
    public function list(TableService $kilik, MemberTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('member/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'member_list_ajax')]
    public function _list(Request $request, TableService $kilik, MemberTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'member_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($member);
            $this->addFlash('success', 'success.member.created');

            return $this->redirectToRoute('member_edit', ['member' => $member->getId()]);
        }

        return $this->render('member/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{member}', name: 'member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($member);
            $this->addFlash('success', 'success.member.updated');

            return $this->redirectToRoute('member_edit', ['member' => $member->getId()]);
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{member}/delete', name: 'member_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Member $member): Response
    {
        if (!$this->repository->isRemovable($member)) {
            $this->addFlash('warning', 'warning.member.not_removable');

            return $this->redirectToRoute('member_edit', ['member' => $member->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($member);
            $this->addFlash('success', 'success.member.deleted');

            return $this->redirectToRoute('member_list');
        }

        return $this->render('member/delete.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }
}
