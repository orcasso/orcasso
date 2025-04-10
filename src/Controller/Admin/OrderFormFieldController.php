<?php

namespace App\Controller\Admin;

use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\User;
use App\Form\OrderFormFieldType;
use App\Repository\OrderFormFieldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order-form-fields')]
#[IsGranted(User::ROLE_ADMIN_ORDER_FORM_EDIT)]
final class OrderFormFieldController extends AbstractController
{
    public function __construct(protected OrderFormFieldRepository $repository)
    {
    }

    #[Route('/new/{orderForm}', name: 'admin_order_form_field_create', methods: ['GET', 'POST'])]
    public function create(Request $request, OrderForm $orderForm): Response
    {
        $field = new OrderFormField($orderForm);
        $form = $this->createForm(OrderFormFieldType::class, $field);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($field);
            $this->addFlash('success', 'success.order_form_field.created');

            return $this->redirectToRoute('admin_order_form_field_edit', ['field' => $field->getId()]);
        }

        return $this->render('admin/order_form_field/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{field}', name: 'admin_order_form_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrderFormField $field): Response
    {
        $form = $this->createForm(OrderFormFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($field);
            $this->addFlash('success', 'success.order_form_field.updated');

            return $this->redirectToRoute('admin_order_form_field_edit', ['field' => $field->getId()]);
        }

        return $this->render('admin/order_form_field/edit.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{field}/delete', name: 'admin_order_form_field_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, OrderFormField $field): Response
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($field);
            $this->addFlash('success', 'success.order_form_field.deleted');

            return $this->redirectToRoute('admin_order_form_edit', ['orderForm' => $field->getForm()->getId()]);
        }

        return $this->render('admin/order_form_field/delete.html.twig', [
            'field' => $field,
            'form' => $form->createView(),
        ]);
    }
}
