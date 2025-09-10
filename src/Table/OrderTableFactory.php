<?php

namespace App\Table;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\FilterSelect;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderTableFactory implements TableFactoryInterface
{
    public function __construct(protected OrderRepository $repository, protected TranslatorInterface $translator,
        protected RouterInterface $router, protected Environment $twig)
    {
    }

    public function getTableId(): string
    {
        return 'order_table';
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('o');
        $queryBuilder->innerJoin('o.member', 'm');
        $member = 'CONCAT(m.firstName, \' \', m.lastName)';
        $queryBuilder->addSelect('m', $member.' AS member');
        $lineConcat = 'GROUP_CONCAT(l.label SEPARATOR \' | \')';
        $queryBuilder->addSelect($lineConcat.' AS lines')->leftjoin('o.lines', 'l')->groupBy('o.id');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_order_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'o')
            ->setEntityLoaderRepository(Order::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_order_edit',
                'identifier_name' => 'order',
            ])
            ->addColumn(
                (new Column())->setLabel('order.label.identifier')->setTranslateDomain('forms')
                    ->setSort(['o.identifier' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('o.identifier')
                        ->setName('o_identifier')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('order.label.member')->setTranslateDomain('forms')
                    ->setSort(['member' => 'ASC'])
                    ->setFilter((new Filter())
                        ->setField($member)
                        ->setName('member')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('order.label.lines')->setTranslateDomain('forms')
                    ->setSort(['lines' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField($lineConcat)
                        ->setName('lines')
                        ->setHaving(true)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('order.label.total_amount')->setTranslateDomain('forms')
                    ->setSort(['o.totalAmount' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('o.totalAmount')
                        ->setName('o_totalAmount')
                    )
                    ->setDisplayCallback(fn ($value, $row) => number_format($value, 2, ',', '').' €')
                ->setDisplayClass('text-center')
            )
            ->addColumn(
                (new Column())->setLabel('order.label.paid_amount')->setTranslateDomain('forms')
                    ->setSort(['o.paidAmount' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('o.paidAmount')
                        ->setName('o_paidAmount')
                    )
                    ->setDisplayCallback(function ($value, $row) {
                        /** @var Order $order */
                        $order = $row['object'];
                        $class = $order->getPaidAmount() < $order->getTotalAmount() ? ($order->getPaidAmount() > 0 ? 'bg-warning' : 'bg-danger') : 'bg-success';

                        return '<div class="text-center '.$class.'">'.number_format($value, 2, ',', '').' €</div>';
                    })
                    ->setRaw(true)
            )
            ->addColumn(
                (new Column())->setLabel('order.label.status')->setTranslateDomain('forms')
                    ->setSort(['o.status' => 'asc'])
                    ->setFilter((new FilterSelect())
                        ->setField('o.status')
                        ->setName('o_status')
                        ->setChoices(Order::STATUSES)
                        ->setChoiceLabel(fn (string $status) => "order.choice.status.$status")
                        ->setChoiceTranslationDomain('forms')
                        ->setPlaceholder('--')
                    )
                    ->setDisplayCallback(fn ($value, $row) => $this->translator->trans("order.choice.status.$value", [], 'forms'))
                    ->setDisplayCallback(function ($value, $row) {
                        $class = Order::STATUS_VALIDATED === $value ? '' : (Order::STATUS_PENDING === $value ? 'text-warning' : 'text-danger');

                        return '<span class="'.$class.'">'.$this->translator->trans("order.choice.status.$value", [], 'forms').'</span>';
                    })
                    ->setRaw(true)
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['o.createdAt' => 'asc', 'o.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('o.createdAt')
                            ->setName('o_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
