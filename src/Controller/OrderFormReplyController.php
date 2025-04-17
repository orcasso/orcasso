<?php

namespace App\Controller;

use App\Entity\MemberDocument;
use App\Entity\OrderForm;
use App\Entity\OrderFormField;
use App\Entity\OrderFormReply;
use App\Form\OrderFormReplyType;
use App\Repository\MemberDocumentRepository;
use App\Repository\MemberRepository;
use App\Repository\OrderFormReplyRepository;
use App\Repository\OrderRepository;
use App\Transformer\OrderFormReplyToOrder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order-form-reply')]
final class OrderFormReplyController extends AbstractController
{
    public function __construct(protected OrderFormReplyRepository $repository, protected OrderRepository $orderRepository,
        protected MemberRepository $memberRepository, protected MemberDocumentRepository $memberDocumentRepository)
    {
    }

    #[Route('/{orderForm}', name: 'order_form_reply', methods: ['GET', 'POST'])]
    public function reply(Request $request, OrderForm $orderForm, OrderFormReplyToOrder $toOrder): Response
    {
        $reply = new OrderFormReply($orderForm);
        $form = $this->createForm(OrderFormReplyType::class, $reply);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reply->applyMemberData();
            $this->repository->update($reply);
            $this->addFlash('success', 'success.order_form_reply.created');

            try {
                $order = $toOrder->toOrder($reply);
                $this->memberRepository->update($order->getMember(), false);
                $this->orderRepository->update($order);
                foreach ($orderForm->getFields() as $field) {
                    if (OrderFormField::TYPE_DOCUMENT !== $field->getType()) {
                        continue;
                    }
                    $value = $form->get(OrderFormReplyType::getFieldName($field))->getData();
                    if (!$value instanceof UploadedFile) {
                        continue;
                    }
                    $this->memberDocumentRepository->storeFromUploadedFile(new MemberDocument($order->getMember()), $value);
                }
                $this->addFlash('success', 'success.order.created');
            } catch (\Exception $exception) {
                $this->addFlash('danger', 'Unable to store order');
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('order_form_reply/reply.html.twig', [
            'order_form' => $orderForm,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{orderForm}/_total-amount', name: 'order_form_reply_show_total_amount_ajax', methods: ['POST'])]
    public function _showTotalAmount(Request $request, OrderForm $orderForm): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException('Invalid request');
        }

        $reply = new OrderFormReply($orderForm);
        $form = $this->createForm(OrderFormReplyType::class, $reply);
        $form->handleRequest($request);

        return $this->render('order_form_reply/_showTotalAmount.html.twig', [
            'total_amount' => $reply->calculateTotalAmount(),
        ]);
    }
}
