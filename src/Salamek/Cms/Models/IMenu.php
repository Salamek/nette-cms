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
    public function getName();

    /**
     * @return string
     */
    public function getLatteTemplate();

    /**
     * @return string
     */
    public function getLayoutName();

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string
     */
    public function getMetaRobots();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getH1();

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