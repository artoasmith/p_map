<?php
/**
 * Created by PhpStorm.
 * User: artoa
 * Date: 26.07.2016
 * Time: 14:37
 */

namespace PokemonBundle\EventListener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;

class MenuBuilderListener
{
    public function addMenuItems(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $child = $menu->addChild('reports', array(
            'route' => '/admin/dashboard',
            'labelAttributes' => array('icon' => 'fa fa-bar-chart'),
        ));

        $child->setLabel('Daily and monthly reports');
    }
}