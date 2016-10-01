<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenu
{
    /**
     * @param IMenu|null $parent
     */
    public function setParent(IMenu $parent = null);

    /**
     * @param boolean $isHomePage
     */
    public function setIsHomePage($isHomePage);

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param boolean $isHidden
     */
    public function setIsHidden($isHidden);

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive);

    /**
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots($metaRobots);

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @param string $h1
     */
    public function setH1($h1);

    /**
     * @param float $sitemapPriority
     */
    public function setSitemapPriority($sitemapPriority);

    /**
     * @param boolean $isSitemap
     */
    public function setIsSitemap($isSitemap);

    /**
     * @param boolean $isShowH1
     */
    public function setIsShowH1($isShowH1);

    /**
     * @param string $latteTemplate
     */
    public function setLatteTemplate($latteTemplate);

    /**
     * @param string $presenter
     */
    public function setPresenter($presenter);

    /**
     * @param string $action
     */
    public function setAction($action);

    /**
     * @param boolean $isSystem
     */
    public function setIsSystem($isSystem);

    /**
     * @param $parameters
     * @param callable $parameterSumGenerator
     * @return void
     */
    public function setParameters($parameters, callable $parameterSumGenerator);

    /**
     * @param boolean $isRegularExpression
     */
    public function setIsRegularExpression($isRegularExpression);

    /**
     * @param boolean $isRegularExpressionMatchArguments
     */
    public function setIsRegularExpressionMatchArguments($isRegularExpressionMatchArguments);

    /**
     * @param string $layoutName
     */
    public function setLayoutName($layoutName);

    /**
     * @return boolean
     */
    public function isHomePage();
    
    /**
     * @param $locale
     */
    public function setTranslatableLocale($locale);

    /**
     * @return mixed
     */
    public function getParent();

    /**
     * @return mixed
     */
    public function getChildren();

    /**
     * @return mixed
     */
    public function getRoot();

    /**
     * @return mixed
     */
    public function getLvl();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLatteTemplate();

    /**
     * @return boolean
     */
    public function isHidden();

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @return string
     */
    public function getLayoutName();

    /**
     * @return boolean
     */
    public function isIsHidden();

    /**
     * @return boolean
     */
    public function isIsActive();

    /**
     * @return string
     */
    public function getSlug();

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
    public function isIsHomePage();

    /**
     * @return float
     */
    public function getSitemapPriority();

    /**
     * @return boolean
     */
    public function isSitemap();

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
     * @return boolean
     */
    public function isSystem();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return boolean
     */
    public function isRegularExpression();

    /**
     * @return boolean
     */
    public function isRegularExpressionMatchArguments();

    /**
     * @return IMenuContent[]
     */
    public function getMenuContents();
}