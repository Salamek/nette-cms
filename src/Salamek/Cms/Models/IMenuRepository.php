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
     * @return mixed|null|IMenu
     */
    public function getOneById($id);

    /**
     * @param $name
     * @return mixed|null|IMenu
     */
    public function getByName($name);

    /**
     * @param $name
     * @param IMenu $parentMenu
     * @param IMenu|null $ignoreMenu
     * @return bool
     */
    public function isNameFree($name, IMenu $parentMenu = null, IMenu $ignoreMenu = null);

    /**
     * @return void
     */
    public function resetIsHomePage();

    /**
     * @param $presenter
     * @param $action
     * @param array $parameters
     * @return mixed
     */
    public function getByPresenterAndActionAndParameters($presenter, $action, $parameters = []);

    /**
     * @param IMenu $child
     * @param IMenu $root
     */
    public function persistAsLastChildOf(IMenu $child, IMenu $root);

    /**
     * @param $slug
     * @param array $parameters
     * @return mixed
     */
    public function getBySlug($slug, $parameters = []);

    /**
     * @return IMenu
     */
    public function getHomePage();

    /**
     * @return IMenu[]
     */
    public function getAll();

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
}