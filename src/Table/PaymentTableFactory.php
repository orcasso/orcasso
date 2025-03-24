<?php

namespace App\Table;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\FilterSelect;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentTableFactory
{
    public function __construct(protected PaymentRepository $repository, protected RouterInterface $router, protected TranslatorInterface $translator)
    {
    }

    public function getTableId(): string
    {
        return 'payment_table';
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('p');
        $queryBuilder->innerJoin('p.member', 'm');
        $member = 'CONCAT(m.firstName, \' \', m.lastName)';
        $queryBuilder->addSelect('m', $member.' AS member');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('payment_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'p')
            ->setEntityLoaderRepository(Payment::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'payment_edit',
                'identifier_name' => 'payment',
            ])
            ->addColumn(
                (new Column())->setLabel('payment.label.identifier')->setTranslateDomain('forms')
                    ->setSort(['p.identifier' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('p.identifier')
                        ->setName('p_identifier')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('payment.label.member')->setTranslateDomain('forms')
                    ->setSort(['member' => 'ASC'])
                    ->setFilter((new Filter())
                        ->setField($member)
                        ->setName('member')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('payment.label.issued_at')->setTranslateDomain('forms')
                    ->setSort(['p.issuedAt' => 'asc', 'p.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('p.issuedAt')
                            ->setName('p_issuedAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('payment.label.received_at')->setTranslateDomain('forms')
                    ->setSort(['p.receivedAt' => 'asc', 'p.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('p.receivedAt')
                            ->setName('p_receivedAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('payment.label.amount')->setTranslateDomain('forms')
                    ->setSort(['p.amount' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('p.amount')
                        ->setName('p_amount')
                    )
                    ->useTotal()
                    ->setDisplayCallback(fn ($value) => number_format($value, 2, ',', ' ').' €')
            )
            ->addColumn(
                (new Column())->setLabel('payment.label.method')->setTranslateDomain('forms')
                    ->setSort(['p.method' => 'asc'])
                    ->setFilter((new FilterSelect())
                        ->setField('p.method')
                        ->setName('p_method')
                        ->setChoices(Payment::METHODS)
                        ->setChoiceLabel(fn (string $method) => "payment.choice.method.$method")
                        ->setChoiceTranslationDomain('forms')
                        ->setPlaceholder('--')
                    )
                    ->setDisplayCallback(fn ($value, $row) => $this->translator->trans("payment.choice.method.$value", [], 'forms'))
            )
        ;

        return $table;
    }
}
