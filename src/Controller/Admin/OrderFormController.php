<?php

namespace App\Controller\Admin;

use App\Entity\OrderForm;
use App\Entity\User;
use App\Form\OrderFormType;
use App\Repository\OrderFormRepository;
use App\Table\OrderFormTableFactory;
use Kilik\TableBundle\Services\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order-forms')]
#[IsGranted(User::ROLE_ADMIN_ORDER_FORM_EDIT)]
final class OrderFormController extends AbstractController
{
    public function __construct(protected OrderFormRepository $repository)
    {
    }

    #[Route('/', name: 'admin_order_form_list')]
    public function list(TableService $kilik, OrderFormTableFactory $tableFactory): Response
    {
        $table = $tableFactory->getTable();

        return $this->render('admin/order_form/list.html.twig', [
            'table' => $kilik->createFormView($table),
        ]);
    }

    #[Route('/_list', name: 'admin_order_form_list_ajax')]
    public function _list(Request $request, TableService $kilik, OrderFormTableFactory $tableFactory): Response
    {
        return $kilik->handleRequest($tableFactory->getTable(), $request);
    }

    #[Route('/new', name: 'admin_order_form_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $orderForm = new OrderForm();
        $form = $this->createForm(OrderFormType::class, $orderForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($orderForm);
            $this->addFlash('success', 'success.order_form.created');

            return $this->redirectToRoute('admin_order_form_edit', ['orderForm' => $orderForm->getId()]);
        }

        return $this->render('admin/order_form/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{orderForm}', name: 'admin_order_form_edit', methods: ['GET', 'POST'])]
    public function edit(OrderForm $orderForm): Response
    {
        return $this->render('admin/order_form/edit.html.twig', [
            'order_form' => $orderForm,
        ]);
    }

    #[Route('/{orderForm}/edit', name: 'admin_order_form_edit_header', methods: ['GET', 'POST'])]
    public function editHeader(Request $request, OrderForm $orderForm): Response
    {
        $form = $this->createForm(OrderFormType::class, $orderForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($orderForm);
            $this->addFlash('success', 'success.order_form.updated');

            return $this->redirectToRoute('admin_order_form_edit', ['orderForm' => $orderForm->getId()]);
        }

        return $this->render('admin/order_form/edit_header.html.twig', [
            'order_form' => $orderForm,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{orderForm}/delete', name: 'admin_order_form_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, OrderForm $orderForm): Response
    {
        if (!$this->repository->isRemovable($orderForm)) {
            $this->addFlash('warning', 'warning.order_form.not_removable');

            return $this->redirectToRoute('admin_order_form_edit', ['orderForm' => $orderForm->getId()]);
        }

        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($orderForm);
            $this->addFlash('success', 'success.order_form.deleted');

            return $this->redirectToRoute('admin_order_form_list');
        }

        return $this->render('admin/order_form/delete.html.twig', [
            'form' => $form->createView(),
            'order_form' => $orderForm,
        ]);
    }
}
