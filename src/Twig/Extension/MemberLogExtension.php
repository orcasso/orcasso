<?php

namespace App\Twig\Extension;

use App\Entity\LegalRepresentative;
use App\Entity\MemberLog;
use App\Entity\MemberLogObjectInterface;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MemberLogExtension extends AbstractExtension
{
    public function __construct(protected RouterInterface $router, protected ManagerRegistry $registry,
        protected TranslatorInterface $translator)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('member_log_action_icon', $this->getActionIcon(...)),
            new TwigFunction('member_log_object_friendly_name', $this->getObjectFriendlyName(...)),
            new TwigFunction('member_log_object_show_url', $this->getObjectShowUrl(...)),
        ];
    }

    public function getActionIcon(MemberLog $log): string
    {
        return match ($log->getAction()) {
            $log::ACTION_CREATE => 'far fa-plus-square text-success',
            $log::ACTION_UPDATE => 'far fa-edit text-primary',
            $log::ACTION_REMOVE => 'far fa-minus-square text-danger',
        };
    }

    public function getObject(MemberLog $log): ?MemberLogObjectInterface
    {
        return $this->registry->getRepository($log->getObjectClass())->find($log->getObjectId());
    }

    public function getObjectFriendlyName(MemberLog $log): string
    {
        if (null === $object = $this->getObject($log)) {
            return $this->translator->trans("member_log.choice.objectClass.{$log->getObjectClass()}", domain: 'forms').' #'.$log->getObjectId();
        }

        return $object->getFriendlyName();
    }

    public function getObjectShowUrl(MemberLog $log): ?string
    {
        if (null === $object = $this->getObject($log)) {
            return null;
        }

        return match ($object::class) {
            LegalRepresentative::class => $this->router->generate('admin_legal_representative_edit', ['representative' => $log->getObjectId()]),
            Order::class => $this->router->generate('admin_order_edit', ['order' => $log->getObjectId()]),
            OrderLine::class => $this->router->generate('admin_order_edit', ['order' => $object->getOrder()->getId()]),
            Payment::class => $this->router->generate('admin_payment_edit', ['payment' => $log->getObjectId()]),
            PaymentOrder::class => $this->router->generate('admin_payment_edit', ['payment' => $object->getPayment()->getId()]),
            default => null,
        };
    }
}
