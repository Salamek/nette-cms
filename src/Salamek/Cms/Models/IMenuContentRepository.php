<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IMenuContentRepository
{
    /**
     * @param IMenu $menu
     * @param IComponentAction $componentAction
     * @param array $parameters
     * @return array
     */
    public function getByMenuAndComponentActionAndParameters(IMenu $menu, IComponentAction $componentAction, array $parameters);
}