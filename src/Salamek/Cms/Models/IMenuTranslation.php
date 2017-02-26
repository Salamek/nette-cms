<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenuTranslation
{
    /**
     * @return string
     */
    public function getName();

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
    public function getTitle();

    /**
     * @return string
     */
    public function getH1();

    /**
     * @return IMenu
     */
    public function getIMenu();
}