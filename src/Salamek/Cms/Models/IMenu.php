<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenu
{
    /**
     * @return boolean
     */
    public function isHomePage();

    /**
     * @return string
     */
    public function getLatteTemplate();

    /**
     * @return string
     */
    public function getLayoutName();

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getMetaRobots();

    /**
     * @return boolean
     */
    public function isShowH1();

    /**
     * @return string
     */
    public function getPresenter();

    /**
     * @return string
     */
    public function getAction();

    /**
     * @return array
     */
    public function getParameters();
    
    /**
     * @return IMenuContent[]
     */
    public function getMenuContents();
}