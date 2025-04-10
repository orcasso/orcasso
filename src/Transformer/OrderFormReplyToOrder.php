<?php

namespace App\Transformer;

use App\Entity\Order;
use App\Entity\OrderFormReply;
use App\Entity\OrderLine;
use App\Repository\MemberRepository;

class OrderFormReplyToOrder
{
    public function __construct(protected MemberRepository $memberRepository)
    {
    }

    public function toOrder(OrderFormReply $reply): Order
    {
        $member = $this->memberRepository->findOneBy([
            'firstName' => $reply->getMemberData()->firstName,
            'lastName' => $reply->getMemberData()->lastName]
        );
        $member = $reply->getMemberData()->toMember($member);
        $order = (new Order())->setMember($member)->setNotes('');
        $reply->setOrder($order);

        $form = $reply->getForm();

        $order->addLine(OrderLine::createSimple($order)->setAmount($form->getOrderMainLineAmount())->setLabel($form->getOrderMainLineLabel()));
        foreach ($reply->getForm()->getFields() as $field) {
            if (null === $repliedChoice = $reply->getFieldChoice($field)) {
                continue;
            }
            if ($repliedChoice->getAllowanceLabel() && !$repliedChoice->getAllowancePercentage()) {
                continue;
            }
            $repliedChoice->toOrderLine($order);
        }

        return $order;
    }
}
