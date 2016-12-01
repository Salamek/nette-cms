<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms;


use Salamek\Cms\Models\ILocaleRepository;
use Salamek\Cms\Models\IMenuRepository;
use WebLoader\Nette\LoaderFactory;

trait TCmsPresenter
{
    /** @var Cms */
    private $cms;

    /** @var IMenuRepository */
    private $menuRepository;

    /** @var ILocaleRepository */
    private $localeRepository;

    /**
     * @param Cms $cms
     */
    public function injectCms(Cms $cms)
    {
        $this->cms = $cms;
    }

    /**
     * @param IMenuRepository $IMenuRepository
     */
    public function injectMenuRepository(IMenuRepository $IMenuRepository)
    {
        $this->menuRepository = $IMenuRepository;
    }

    /**
     * @param LoaderFactory $webLoader
     */
    public function injectLoaderFactory(LoaderFactory $webLoader)
    {
        $this->webLoader = $webLoader;
    }

    /**
     * @param ILocaleRepository $localeRepository
     */
    public function injectLocaleRepository(ILocaleRepository $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    public final function renderDefault()
    {
        $menu = $this->menuRepository->getOneById($this->menuId, $this->localeRepository->getCurrentLocale());
        $this->setLayout($menu->getLayoutName());
        $this->template->metaDescription = $menu->getMetaDescription();
        $this->template->title = $menu->getTitle();
        $this->template->metaKeywords = $menu->getMetaKeywords();
        $this->template->metaRobots = $menu->getMetaRobots();
        $this->template->h1 = $menu->getH1();
        $this->template->showH1 = $menu->isShowH1();
        $this->template->bodyClass = ($menu->isHomePage() ? 'homepage': 'subpage');
    }

    /**
     * Formats layout template file names.
     * @return array
     */
    public final function formatLayoutTemplateFiles()
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $className = trim(str_replace($presenter . 'Presenter', '', get_class($this)), '\\');
        $exploded = explode('\\', $className);
        $moduleName = str_replace('Module', '', end($exploded));
        $layout = $this->layout ? $this->layout : 'layout';
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        $list = parent::formatLayoutTemplateFiles();
        do {
            $list[] = $this->cms->getLayoutDir()."/@$layout.latte";
            $dir = dirname($dir);
        } while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
        return $list;
    }
}