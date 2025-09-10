<?php

namespace App\Table;

use App\Entity\OrderForm;
use App\Repository\OrderFormRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderFormTableFactory implements TableFactoryInterface
{
    public function __construct(protected OrderFormRepository $repository, protected TranslatorInterface $translator,
        protected RouterInterface $router, protected Environment $twig)
    {
    }

    public function getTableId(): string
    {
        return 'form_table';
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('f');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_order_form_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'f')
            ->setDefaultIdentifierFieldNames()
            ->setEntityLoaderRepository(OrderForm::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_order_form_edit',
                'identifier_name' => 'orderForm',
            ])
            ->addColumn(
                (new Column())->setLabel('order_form.label.title')->setTranslateDomain('forms')
                    ->setSort(['f.title' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('f.title')
                        ->setName('f_title')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('order_form.label.order_main_line_amount')->setTranslateDomain('forms')
                    ->setSort(['f.orderMainLineAmount' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('f.orderMainLineAmount')
                        ->setName('f_orderMainLineAmount')
                    )
                    ->setDisplayCallback(fn ($value) => number_format($value, 2, ',', '').' €')
            )
            ->addColumn(
                (new Column())->setLabel('order_form.label.enabled')->setTranslateDomain('forms')
                    ->setSort(['f.enabled' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('f.enabled')
                        ->setName('f_enabled')
                    )
                    ->setDisplayCallback(fn ($value, $row) => '<i class="fa '.($value ? 'fa-toggle-on text-success' : 'fa-toggle-off').'"></i>')
                    ->setRaw(true)
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['f.createdAt' => 'asc', 'a.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('f.createdAt')
                            ->setName('f_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.updated_at')->setTranslateDomain('forms')
                    ->setSort(['f.updatedAt' => 'asc', 'f.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('f.updatedAt')
                            ->setName('f_updatedAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
