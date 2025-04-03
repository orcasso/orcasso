<?php

namespace App\Utils;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder
{
    public function __construct(protected FactoryInterface $factory, protected Security $security,
        protected AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function createMainMenu(): ItemInterface
    {
        $menu = $this->factory
            ->createItem('root')
            ->setChildrenAttributes([
                'class' => 'nav nav-pills nav-sidebar flex-column',
                'data-widget' => 'treeview',
                'role' => 'menu',
                'data-accordion' => 'false',
            ]);

        $menu->addChild('_menu.homepage', [
            'route' => 'admin_dashboard',
            'extras' => ['icon_class' => 'fa fa-home'],
        ]);

        $menu->addChild('_menu.activity', [
            'route' => 'admin_activity_list',
            'extras' => ['icon_class' => 'fas fa-volleyball-ball'],
        ]);
        $menu->addChild('_menu.member', [
            'route' => 'admin_member_list',
            'extras' => ['icon_class' => 'fas fa-users'],
        ]);
        $menu->addChild('_menu.order', [
            'route' => 'admin_order_list',
            'extras' => ['icon_class' => 'fas fa-shopping-cart'],
        ]);
        $menu->addChild('_menu.payment', [
            'route' => 'admin_payment_list',
            'extras' => ['icon_class' => 'fas fa-credit-card'],
        ]);

        $this->buildConfigurationMenu($menu);

        return $menu;
    }

    protected function buildConfigurationMenu(ItemInterface $menu): void
    {
        $menu->addChild('_menu.separation.configuration', [
            'attributes' => ['class' => 'nav-header text-uppercase'],
        ]);

        $menu->addChild('_menu.user', [
            'route' => 'admin_user_list',
            'extras' => [
                'icon_class' => ' fa fa-user-shield',
                'routes' => [
                    ['pattern' => '/^admin_user_/'],
                ],
            ],
        ]);
    }
}
