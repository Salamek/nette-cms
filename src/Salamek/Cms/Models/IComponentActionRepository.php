<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IComponentActionRepository
{

    /**
     * @param $id
     * @return mixed|null|IComponentAction
     */
    public function getOneById($id);

    /**
     * @param $moduleName
     * @param $componentName
     * @param $componentActionName
     * @return IComponentAction
     */
    public function getByModuleNameAndComponentNameAndComponentActionName($moduleName, $componentName, $componentActionName);
}