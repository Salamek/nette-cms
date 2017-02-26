<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenuRepository
{
    /**
     * @param $id
     * @return IMenu|null
     */
    public function getOneById($id);

    /**
     * @param $presenter
     * @param $action
     * @return IMenu|null
     */
    public function getOneByPresenterAction($presenter, $action);

    /**
     * @return IMenu[]
     */
    public function getAll();

    /**
     * @param $identifier
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
     * @return mixed
     */
    public function createNewMenu(
        $identifier,
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
        $layoutName = 'layout'
    );

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