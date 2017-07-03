<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms;


use Salamek\Cms\Models\ILocaleRepository;
use Salamek\Cms\Models\IMenuRepository;
use Salamek\Cms\Models\IMenuTranslationRepository;
use WebLoader\Nette\LoaderFactory;

trait TCmsPresenter
{
    /** @var Cms */
    private $cms;

    /** @var IMenuRepository */
    private $menuRepository;

    /** @var ILocaleRepository */
    private $localeRepository;

    /** @var IMenuTranslationRepository */
    private $menuTranslationRepository;

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

    /**
     * @param IMenuTranslationRepository $menuTranslationRepository
     */
    public function injectMenuTranslationRepository(IMenuTranslationRepository $menuTranslationRepository)
    {
        $this->menuTranslationRepository = $menuTranslationRepository;
    }

    public final function renderDefault()
    {
        $menu = $this->menuRepository->getOneById($this->menuId);
        $this->setLayout($menu->getLayoutName());


        $translated = $this->menuTranslationRepository->getOneByMenu($menu, $this->localeRepository->getCurrentLocale());

        $this->template->identifier = $menu->getIdentifier();
        $this->template->metaDescription = $translated->getMetaDescription();
        $this->template->title = $translated->getTitle();
        $this->template->metaKeywords = $translated->getMetaKeywords();
        $this->template->metaRobots = $menu->getMetaRobots();
        $this->template->h1 = $translated->getH1();
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
    
    /**
     * @param $name
     * @param array $parameters
     * @return mixed
     */
    public function cmsLink($name, array $parameters = [])
    {
        return $this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters));
    }

    /**
     * @param $name
     * @param array $parameters
     */
    public function cmsRedirect($name, array $parameters = [])
    {
        $this->redirectUrl($this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters)));
    }
}
