<?php

namespace Salamek\Cms;

/**
 * Description of iCmsActionData
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsActionOption
{
    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * @param string $metaDescription
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string $metaKeywords
     * @return void
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param string $metaRobots
     * @return void
     */
    public function setMetaRobots($metaRobots);

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters);
    
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTitle();

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
     * @return array
     */
    public function getParameters();

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name);
}
