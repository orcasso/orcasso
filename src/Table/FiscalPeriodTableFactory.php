<?php

namespace App\Table;

use App\Entity\FiscalPeriod;
use App\Entity\User;
use App\Repository\FiscalPeriodRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FiscalPeriodTableFactory implements TableFactoryInterface
{
    public function __construct(
        private readonly FiscalPeriodRepository $repository,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
    ) {
    }

    public function getTableId(): string
    {
        return 'fiscal_period_table';
    }

    public function getExpectedRole(): string
    {
        return User::ROLE_ADMIN_FISCAL_PERIOD_EDIT;
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('fp')
            ->orderBy('fp.current', 'DESC')
            ->addOrderBy('fp.name', 'DESC');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_fiscal_period_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'fp')
            ->setDefaultIdentifierFieldNames()
            ->setEntityLoaderRepository(FiscalPeriod::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_fiscal_period_edit',
                'identifier_name' => 'fiscalPeriod',
            ])
            ->addColumn(
                (new Column())->setLabel('fiscal_period.label.name')->setTranslateDomain('forms')
                    ->setSort(['fp.name' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('fp.name')
                        ->setName('fp_name')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('fiscal_period.label.is_current')->setTranslateDomain('forms')
                    ->setName('fp_current')
                    ->setSort(['fp.current' => 'desc'])
                    ->setDisplayCallback(fn ($value) => $value
                        ? '<span class="badge badge-success">'.$this->translator->trans('_meta.word.yes').'</span>'
                        : '<span class="text-muted">'.$this->translator->trans('_meta.word.no').'</span>')
                    ->setRaw(true)
                    ->setDisplayClass('text-center')
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['fp.createdAt' => 'asc', 'fp.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('fp.createdAt')
                            ->setName('fp_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
