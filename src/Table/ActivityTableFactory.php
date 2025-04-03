<?php

namespace App\Table;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ActivityTableFactory
{
    public function __construct(protected ActivityRepository $repository, protected TranslatorInterface $translator,
        protected RouterInterface $router, protected Environment $twig)
    {
    }

    public function getTableId(): string
    {
        return 'activity_table';
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('a');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_activity_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'a')
            ->setDefaultIdentifierFieldNames()
            ->setEntityLoaderRepository(Activity::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_activity_edit',
                'identifier_name' => 'activity',
            ])
            ->addColumn(
                (new Column())->setLabel('activity.label.name')->setTranslateDomain('forms')
                    ->setSort(['a.name' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('a.name')
                        ->setName('a_name')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['a.createdAt' => 'asc', 'a.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('a.createdAt')
                            ->setName('a_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.updated_at')->setTranslateDomain('forms')
                    ->setSort(['a.updatedAt' => 'asc', 'a.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('a.updatedAt')
                            ->setName('a_updatedAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
