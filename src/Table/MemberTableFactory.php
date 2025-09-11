<?php

namespace App\Table;

use App\Entity\Member;
use App\Entity\User;
use App\Repository\MemberRepository;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\Table;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MemberTableFactory implements TableFactoryInterface
{
    public function __construct(protected MemberRepository $repository, protected TranslatorInterface $translator,
        protected RouterInterface $router, protected Environment $twig)
    {
    }

    public function getTableId(): string
    {
        return 'member_table';
    }

    public function getExpectedRole(): string
    {
        return User::ROLE_ADMIN_MEMBER_EDIT;
    }

    public function getTable(): Table
    {
        $queryBuilder = $this->repository->createQueryBuilder('m');
        $queryBuilder->addSelect('TIMESTAMPDIFF(YEAR, m.birthDate, CURRENT_DATE()) AS age');

        $table = (new Table())
            ->setId($this->getTableId())
            ->setPath($this->router->generate('admin_member_list_ajax'))
            ->setQueryBuilder($queryBuilder, 'm')
            // ->setDefaultIdentifierFieldNames()
            ->setEntityLoaderRepository(Member::class)
            ->setTemplate('_table/_table.html.twig')
            ->setTemplateParams([
                'show_route_name' => 'admin_member_show',
                'identifier_name' => 'member',
            ])
            ->addColumn(
                (new Column())->setLabel('member.label.first_name')->setTranslateDomain('forms')
                    ->setSort(['m.firstName' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('m.firstName')
                        ->setName('m_firstName')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('member.label.last_name')->setTranslateDomain('forms')
                    ->setSort(['m.lastName' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('m.lastName')
                        ->setName('m_lastName')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('member.label.city')->setTranslateDomain('forms')
                    ->setSort(['m.city' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('m.city')
                        ->setName('m_city')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('member.label.email')->setTranslateDomain('forms')
                    ->setSort(['m.email' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('m.email')
                        ->setName('m_email')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('member.label.phone_number')->setTranslateDomain('forms')
                    ->setSort(['m.phoneNumber' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('m.phoneNumber')
                        ->setName('m_phoneNumber')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.age')->setTranslateDomain('forms')
                    ->setSort(['age' => 'asc'])
                    ->setFilter((new Filter())
                        ->setField('age')
                        ->setName('age')
                        ->setHaving(true)
                        ->setDataFormat(Filter::FORMAT_INTEGER)
                    )
            )
            ->addColumn(
                (new Column())->setLabel('_meta.created_at')->setTranslateDomain('forms')
                    ->setSort(['m.createdAt' => 'asc', 'm.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('m.createdAt')
                            ->setName('m_createdAt')
                            ->setDataFormat(Column::FORMAT_DATE)
                    )
            )
        ;

        return $table;
    }
}
