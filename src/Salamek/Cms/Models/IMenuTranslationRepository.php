<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms\Models;


interface IMenuTranslationRepository
{
    /**
     * @param IMenu $menu
     * @param ILocale|null $locale
     * @return IMenuTranslation|null
     */
    public function getOneByMenu(IMenu $menu, ILocale $locale = null);

    /**
     * @param $slug
     * @param array $parameters
     * @param ILocale|null $locale
     * @return IMenuTranslation|null
     */
    public function getOneBySlug($slug, $parameters = [], ILocale $locale = null);

    /**
     * @param IMenu $menu
     * @param ILocale|null $locale
     * @return mixed
     */
    public function getSlugByMenu(IMenu $menu, ILocale $locale = null);

    /**
     * @param IMenu $menu
     * @param ILocale $locale
     * @param $h1
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $name
     * @param $slug
     * @return mixed
     */
    public function translateMenu(IMenu $menu, ILocale $locale, $h1, $metaDescription, $metaKeywords, $title, $name, $slug = null);
}