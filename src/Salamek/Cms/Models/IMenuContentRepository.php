<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IMenuContentRepository
{
    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return mixed
     */
    public function getOneByMenuFactoryParameters(IMenu $menu, $factory, array $parameters);

    public function saveMenuContent(IMenu $menu, $factory, array $parameters);

    /**
     * @param $id
     * @return IMenuContent
     */
    public function getOneById($id);
}