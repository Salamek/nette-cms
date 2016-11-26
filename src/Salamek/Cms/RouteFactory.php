<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms;


use Nette\Application\Routers\RouteList;

class RouteFactory
{
    /** @var array */
    private $routeFactories = [];

    public function addRouteFactory(IRouterFactory $routeFactory)
    {
        $this->routeFactories[] = $routeFactory;
    }

    public function createRouter()
    {
        $router = new RouteList();
        foreach ($this->routeFactories as $routeFactory) {
            $router[] = $routeFactory->createRouter();
        }

        return $router;
    }
}