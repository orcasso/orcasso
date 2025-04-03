<?php

namespace App\Table;

use App\Entity\User;
use App\Repository\UserRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\FilterSelect;
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
                (new Column())->setLabel('user.label.roles')->setTranslateDomain('forms')
                    ->setSort(['u.roles' => 'asc'])
                    ->setDisplayCallback(function ($value, $row) {
                        $roles = array_map(fn (string $role) => $this->translator->trans('user.choice.roles.'.$role, domain: 'forms'), $value);

                        return '<span class="badge badge-primary" title="'.implode(\PHP_EOL, $roles).'">'.\count($value).'</span>';
                    })
                    ->setFilter(
                        (new FilterSelect())
                            ->setType(Filter::TYPE_LIKE)
                            ->setChoices(User::ROLES)
                            ->setChoiceLabel(fn (string $role) => 'user.choice.roles.'.$role)
                            ->setChoiceTranslationDomain('forms')
                            ->setField('u.roles')
                            ->setName('u_roles')
                            ->setPlaceholder('--')
                    )
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
