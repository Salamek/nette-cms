<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;

interface IComponentRepository
{
    /**
     * @param $id
     * @return mixed|null|IComponent
     */
    public function getById($id);
}