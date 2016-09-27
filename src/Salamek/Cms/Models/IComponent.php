<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IComponent
{
    /**
     * @param IComponentAction $componentAction
     */
    public function addComponentAction(IComponentAction $componentAction);

    /**
     * @param IModule $module
     */
    public function setModule(IModule $module);

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return IModule
     */
    public function getModule();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return IComponentAction[]
     */
    public function getComponentActions();

}