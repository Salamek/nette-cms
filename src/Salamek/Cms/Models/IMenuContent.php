<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenuContent
{
    
    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return IMenu
     */
    public function getMenu();

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getFactory();

}