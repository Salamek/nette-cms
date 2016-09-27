<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IModule
{
    /**
     * @param IComponent $component
     * @return mixed
     */
    public function addComponent(IComponent $component);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return IComponent[]
     */
    public function getComponents();
}