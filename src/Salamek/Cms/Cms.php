<?php

namespace Salamek\Cms;

use Nette;
use Tracy\Debugger;

/**
 * Class TemplatedEmail
 * @package Salamek\TemplatedEmail
 */
class Cms extends Nette\Object
{
    private $tempPath;

    private $presenterNamespace;

    private $layoutDir;

    private $parentClass;

    /** @var ICmsComponentRepository[] */
    private $cmsComponentRepositories = [];

    /** @var array */
    private $cmsComponentFactories;

    /** @var array */
    private $tree = [];

    /** @var array */
    private $mappings = [];
    
    public function __construct($tempPath, $presenterNamespace, $layoutDir, $parentClass, $mappings)
    {
        $this->setTempPath($tempPath);
        $this->setPresenterNamespace($presenterNamespace);
        $this->setLayoutDir($layoutDir);
        $this->setParentClass($parentClass);
        $this->setMappings($mappings);
    }


    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }

    public function addComponentRepository(ICmsComponentRepository $cmsComponentRepository, $module = null, $component = null)
    {
        $this->addRepositoryToTree($cmsComponentRepository, $module, $component);
    }

    public function addComponent($cmsComponentFactory, $module = null, $component = null, $action = null)
    {
        $this->addComponentToTree($cmsComponentFactory, $module, $component, $action);
    }

    public function setPresenterNamespace($presenterNamespace)
    {
        $this->presenterNamespace = $presenterNamespace;
    }

    public function setLayoutDir($layoutDir)
    {
        $this->layoutDir = $layoutDir;
    }

    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }

    /**
     * @param array $mappings
     */
    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @return mixed
     */
    public function getTempPath()
    {
        return $this->tempPath;
    }

    /**
     * @return mixed
     */
    public function getPresenterNamespace()
    {
        return $this->presenterNamespace;
    }

    /**
     * @return mixed
     */
    public function getLayoutDir()
    {
        return $this->layoutDir;
    }

    /**
     * @return mixed
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @return ICmsComponentRepository[]
     */
    public function getCmsComponentRepositories()
    {
        return $this->cmsComponentRepositories;
    }

    /**
     * @return array
     */
    public function getCmsComponentFactories()
    {
        return $this->cmsComponentFactories;
    }

    /**
     * @param ICmsComponentRepository $cmsComponentRepository
     * @param string $module
     * @param string $component
     */
    private function addRepositoryToTree(ICmsComponentRepository $cmsComponentRepository, $module, $component)
    {
        $this->tree[$module][$component]['repository'] = $cmsComponentRepository;
    }

    private function addComponentToTree($cmsComponentFactory, $module, $component, $action)
    {
        $this->tree[$module][$component]['components'][$action] = $cmsComponentFactory;
    }

    /**
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }
}
