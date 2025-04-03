<?php

namespace App\Controller\Admin;

use App\Entity\OrderFormField;
use App\Entity\OrderFormFieldChoice;
use App\Entity\User;
use App\Form\OrderFormFieldChoiceType;
use App\Repository\OrderFormFieldChoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order-forms-field-choices')]
#[IsGranted(User::ROLE_ADMIN_ORDER_FORM_EDIT)]
final class OrderFormFieldChoiceController extends AbstractController
{
    public function __construct(protected OrderFormFieldChoiceRepository $repository)
    {
    }

    #[Route('/new/{field}', name: 'admin_order_form_field_choice_create', methods: ['GET', 'POST'])]
    public function create(Request $request, OrderFormField $field): Response
    {
        $choice = new OrderFormFieldChoice($field);
        $form = $this->createForm(OrderFormFieldChoiceType::class, $choice);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($choice);
            $this->addFlash('success', 'success.order_form_field.updated');

            return $this->redirectToRoute('admin_order_form_field_edit', ['field' => $choice->getField()->getId()]);
        }

        return $this->render('admin/order_form_field_choice/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{choice}', name: 'admin_order_form_field_choice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrderFormFieldChoice $choice): Response
    {
        $form = $this->createForm(OrderFormFieldChoiceType::class, $choice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->update($choice);
            $this->addFlash('success', 'success.order_form_field.updated');

            return $this->redirectToRoute('admin_order_form_field_edit', ['field' => $choice->getField()->getId()]);
        }

        return $this->render('admin/order_form_field_choice/edit.html.twig', [
            'choice' => $choice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{choice}/delete', name: 'admin_order_form_field_choice_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, OrderFormFieldChoice $choice): Response
    {
        $form = $this->createFormBuilder()->setMethod(Request::METHOD_POST)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->remove($choice);
            $this->addFlash('success', 'success.order_form_field.updated');

            return $this->redirectToRoute('admin_order_form_field_edit', ['field' => $choice->getField()->getId()]);
        }

        return $this->render('admin/order_form_field_choice/delete.html.twig', [
            'choice' => $choice,
            'form' => $form->createView(),
        ]);
    }
}
