<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IMenuContentRepository
{
    public function getByOneByMenuFactoryParameters(IMenu $menu, $factory, array $parameters);

    public function saveMenuContent(IMenu $menu, $factory, array $parameters);

    /**
     * @param $id
     * @return IMenuContent
     */
    public function getOneById($id);
}