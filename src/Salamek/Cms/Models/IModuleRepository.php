<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IModuleRepository
{
    /**
     * @return IModule[]
     */
    public function getAll();
}