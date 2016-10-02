<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use Nette;

/**
 * Class TemplateHelpers
 * @package Salamek\Cms
 */
class TemplateHelpers extends Nette\Object
{

    /**
     * @var Cms
     */
    private $cms;

    /**
     * TemplateHelpers constructor.
     * @param Cms $cms
     */
    public function __construct(Cms $cms)
    {
        $this->cms = $cms;
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine)
    {
        if (class_exists('Latte\Runtime\FilterInfo')) {
            $engine->addFilter('cmsLink', [$this, 'cmsLinkFilterAware']);
        } else {
            $engine->addFilter('cmsLink', [$this, 'cmsLink']);
        }
        $engine->addFilter('getCms', [$this, 'getCms']);
    }


    /**
     * @return Cms
     */
    public function getCms()
    {
        return $this->cms;
    }


    /**
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function cmsLink($name, array $parameters = [])
    {
        return $this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters));
    }

    /**
     * @param FilterInfo $filterInfo
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function cmsLinkFilterAware(FilterInfo $filterInfo, $name, array $parameters = [])
    {
        return $this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters));
    }

}
