<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenuRepository
{
    /**
     * @param $id
     * @return array
     */
    public function getById($id);

    /**
     * @param $id
     * @param ILocale|null $locale
     * @return IMenu|null
     */
    public function getOneById($id, ILocale $locale = null);

    /**
     * @param $name
     * @return mixed|null|IMenu
     */
    public function getByName($name);

    /**
     * @param $name
     * @param ILocale $locale
     * @param IMenu|null $parentMenu
     * @param IMenu|null $ignoreMenu
     * @return bool
     */
    public function isNameFree($name, ILocale $locale, IMenu $parentMenu = null, IMenu $ignoreMenu = null);

    /**
     * @return void
     */
    public function resetIsHomePage();

    /**
     * @param IMenu $menu
     * @return IMenu[]
     */
    public function buildParentTree(IMenu $menu);

    /**
     * @param $presenter
     * @param $action
     * @return mixed
     */
    public function getByPresenterAction($presenter, $action);

    /**
     * @param IMenu $child
     * @param IMenu $root
     */
    public function persistAsLastChildOf(IMenu $child, IMenu $root);

    /**
     * @param $slug
     * @param array $parameters
     * @param ILocale|null $locale
     * @return mixed
     */
    public function getBySlug($slug, $parameters = [], $locale = null);

    /**
     * @return IMenu
     */
    public function getHomePage();

    /**
     * @return IMenu[]
     */
    public function getAll();

    /**
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $metaRobots
     * @param $title
     * @param $h1
     * @param bool $isActive
     * @param bool $isHidden
     * @param bool $isHomePage
     * @param float $sitemapPriority
     * @param bool $isSitemap
     * @param bool $isShowH1
     * @param null $presenter
     * @param null $action
     * @param bool $isSystem
     * @param array $parameters
     * @param bool $isRegularExpression
     * @param bool $isRegularExpressionMatchArguments
     * @param string $layoutName
     * @return IMenu
     */
    public function createNewMenu($name,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        $title,
        $h1,
        $isActive = true,
        $isHidden = false,
        $isHomePage = false,
        $sitemapPriority = 0.5,
        $isSitemap = true,
        $isShowH1 = true,
        $presenter = null,
        $action = null,
        $isSystem = false,
        array $parameters = [],
        $isRegularExpression = false,
        $isRegularExpressionMatchArguments = false,
        $layoutName = 'layout');

    /**
     * @param IMenu $menu
     * @param string $latteTemplate
     * @return mixed
     */
    public function saveLatteTemplate(IMenu $menu, $latteTemplate);

    /**
     * @param IMenu $menu
     * @param $presenterName
     * @param $actionName
     * @return mixed
     */
    public function savePresenterAction(IMenu $menu, $presenterName, $actionName);

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return IMenu
     */
    public function getOneByFactoryAndParametersAndIsSystem($factory, array $parameters = [], $isSystem = false);
}