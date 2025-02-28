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
            'route' => 'homepage',
            'extras' => ['icon_class' => 'fa fa-home'],
        ]);

        $menu->addChild('_menu.activity', [
            'route' => 'activity_list',
            'extras' => ['icon_class' => 'fas fa-volleyball-ball'],
        ]);
        $menu->addChild('_menu.member', [
            'route' => 'member_list',
            'extras' => ['icon_class' => 'fas fa-users'],
        ]);

        $this->buildAdministrationMenu($menu);

        return $menu;
    }

    protected function buildAdministrationMenu(ItemInterface $menu): void
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            return;
        }

        $menu->addChild('_menu.separation.administration', [
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
