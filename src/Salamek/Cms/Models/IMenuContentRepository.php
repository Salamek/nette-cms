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

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return mixed
     */
    public function saveMenuContent(IMenu $menu, $factory, array $parameters);

    /**
     * @param $id
     * @return IMenuContent
     */
    public function getOneById($id);

    /**
     * @param IMenu $menu
     * @return mixed
     */
    public function clearMenuContent(IMenu $menu);

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return IMenuContent
     */
    public function getOneByFactoryAndParametersAndIsSystem($factory, array $parameters = [], $isSystem = false);
}