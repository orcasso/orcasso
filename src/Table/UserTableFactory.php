<?php

namespace App\Table;

use App\Entity\User;
use App\Repository\UserRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class UserTableFactory
{
    public function __construct(protected UserRepository $repository, protected TranslatorInterface $translator,
        protected RouterInterface $router, protected Environment $twig)
    {
    }

    public function getTableId(): string
    {
        return 'user_table';
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('u');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_user_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'u')
            ->setDefaultIdentifierFieldNames()
            ->setEntityLoaderRepository(User::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_user_edit',
                'identifier_name' => 'user',
            ])
            ->addColumn(
                (new Column())->setLabel('user.label.name')->setTranslateDomain('forms')
                    ->setSort(['u.name' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('u.name')
                        ->setName('u_name')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('user.label.email')->setTranslateDomain('forms')
                    ->setSort(['u.email' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('u.email')
                        ->setName('u_email')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('user.choice.role.user_admin')->setTranslateDomain('forms')
                    ->setName('u_roles')
                    ->setSort(['u.roles' => 'asc'])
                    ->setDisplayCallback(function ($value, $row) {
                        return $row['object']->hasRole(User::ROLE_ADMIN) ? '<i class="fa fa-check text-success"></i>' : '';
                    })
                    ->setDisplayClass('text-center')
                    ->setRaw(true)
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['u.createdAt' => 'asc', 'u.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('u.createdAt')
                            ->setName('u_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.updated_at')->setTranslateDomain('forms')
                    ->setSort(['u.updatedAt' => 'asc', 'u.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('u.updatedAt')
                            ->setName('u_updatedAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
