<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IComponentAction
{
    /**
     * @return IComponent
     */
    public function getComponent();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return IMenuContent[]
     */
    public function getMenuContents();
}