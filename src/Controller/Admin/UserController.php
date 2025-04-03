<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Table\UserTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/users')]
final class UserController extends AbstractController
{
    public function __construct(protected UserRepository $repository, protected TranslatorInterface $translator,
        protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    #[Route('/', name: 'admin_user_list')]
    public function list(TableService $kilik, UserTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/user/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_user_list_ajax')]
    public function _list(Request $request, TableService $kilik, UserTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->repository->update($user);
            $this->addFlash('success', 'success.user.created');

            return $this->redirectToRoute('admin_user_edit', ['user' => $user->getId()]);
        }

        return $this->render('admin/user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{user}', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plaintextPassword = $form->get('password')->getData()) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $plaintextPassword));
            }
            $this->repository->update($user);
            $this->addFlash('success', 'success.user.updated');

            return $this->redirectToRoute('admin_user_edit', ['user' => $user->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{user}/delete', name: 'admin_user_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, User $user): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        if (!$this->repository->isRemovable($user)) {
            $this->addFlash('warning', 'warning.user.not_removable');

            return $this->redirectToRoute('admin_user_edit', ['user' => $user->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($user);
            $this->addFlash('success', 'success.user.deleted');

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
