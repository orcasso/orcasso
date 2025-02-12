<?php

namespace App\Utils;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MenuBuilder
{
    public function __construct(protected FactoryInterface $factory, protected Security $security)
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
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menu->addChild('menu.dashboard', [
                'route' => 'homepage',
                'extras' => ['icon_class' => 'fa fa-home'],
            ]);
        }

        return $menu;
    }
}
