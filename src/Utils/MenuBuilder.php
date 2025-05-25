<?php

namespace App\Utils;

use App\Entity\User;
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

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_MEMBER_EDIT)) {
            $menu->addChild('_menu.member', [
                'route' => 'admin_member_list',
                'extras' => ['icon_class' => 'fas fa-users'],
            ]);
        }
        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_ORDER_EDIT)) {
            $menu->addChild('_menu.order', [
                'route' => 'admin_order_list',
                'extras' => ['icon_class' => 'fas fa-shopping-cart'],
            ]);
        }
        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_PAYMENT_EDIT)) {
            $menu->addChild('_menu.payment', [
                'route' => 'admin_payment_list',
                'extras' => ['icon_class' => 'fas fa-credit-card'],
            ]);
        }

        $this->buildConfigurationMenu($menu);

        return $menu;
    }

    protected function buildConfigurationMenu(ItemInterface $menu): void
    {
        $menu->addChild('_menu.separation.configuration', [
            'attributes' => ['class' => 'nav-header text-uppercase'],
        ]);

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_USER_EDIT)) {
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

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_ACTIVITY_EDIT)) {
            $menu->addChild('_menu.activity', [
                'route' => 'admin_activity_list',
                'extras' => ['icon_class' => 'fas fa-volleyball-ball'],
            ]);
        }

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_ORDER_FORM_EDIT)) {
            $menu->addChild('_menu.order_form', [
                'route' => 'admin_order_form_list',
                'extras' => [
                    'icon_class' => 'fas fa-file-signature',
                    'routes' => [
                        ['pattern' => '/^admin_order_form_/'],
                    ],
                ],
            ]);
        }

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN_CONFIGURATION_EDIT)) {
            $menu->addChild('_menu.configuration', [
                'route' => 'admin_configuration_edit',
                'extras' => [
                    'icon_class' => 'fas fa-cogs',
                    'routes' => [
                        ['pattern' => '/^admin_configuration_edit/'],
                    ],
                ],
            ]);
        }

        if ('_menu.separation.configuration' === $menu->getLastChild()->getName()) {
            $menu->removeChild('_menu.separation.configuration');
        }
    }
}
