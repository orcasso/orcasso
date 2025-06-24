<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\User;
use App\Form\MemberType;
use App\Repository\MemberLogRepository;
use App\Repository\MemberRepository;
use App\Repository\OrderRepository;
use App\Table\MemberTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/members')]
#[IsGranted(User::ROLE_ADMIN_MEMBER_EDIT)]
final class MemberController extends AbstractController
{
    public function __construct(protected MemberRepository $repository)
    {
    }

    #[Route('/', name: 'admin_member_list')]
    public function list(TableService $kilik, MemberTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/member/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_member_list_ajax')]
    public function _list(Request $request, TableService $kilik, MemberTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_member_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($member);
            $this->addFlash('success', 'success.member.created');

            return $this->redirectToRoute('admin_member_show', ['member' => $member->getId()]);
        }

        return $this->render('admin/member/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{member}', name: 'admin_member_show', methods: ['GET'])]
    public function show(Member $member, OrderRepository $orderRepository, MemberLogRepository $logRepository): Response
    {
        return $this->render('admin/member/show.html.twig', [
            'member' => $member,
            'orders' => $orderRepository->findActivesForMember($member),
            'member_logs' => $logRepository->findBy(['member'=>$member], ['loggedAt'=>'DESC', 'id' => 'DESC']),
        ]);
    }

    #[Route('/{member}/edit', name: 'admin_member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($member);
            $this->addFlash('success', 'success.member.updated');

            return $this->redirectToRoute('admin_member_show', ['member' => $member->getId()]);
        }

        return $this->render('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{member}/delete', name: 'admin_member_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, Member $member): Response
    {
        if (!$this->repository->isRemovable($member)) {
            $this->addFlash('warning', 'warning.member.not_removable');

            return $this->redirectToRoute('admin_member_show', ['member' => $member->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($member);
            $this->addFlash('success', 'success.member.deleted');

            return $this->redirectToRoute('admin_member_list');
        }

        return $this->render('admin/member/delete.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }
}
