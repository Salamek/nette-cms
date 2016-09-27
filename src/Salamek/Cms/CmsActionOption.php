<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Salamek\Cms;

/**
 * Class CmsActionOption
 * @package Salamek\Cms
 */
class CmsActionOption implements ICmsActionOption
{
    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var string */
    private $metaDescription;

    /** @var string */
    private $metaKeywords;

    /** @var string */
    private $metaRobots = 'index, follow';

    /** @var array */
    private $parameters = [];


    /**
     * CmsActionOption constructor.
     * @param $name
     * @param $title
     * @param $metaDescription
     * @param $metaKeywords
     * @param array $parameters
     * @param string $metaRobots
     */
    public function __construct($name, array $parameters = [], $title = null, $metaDescription = null, $metaKeywords = null, $metaRobots = 'index, follow')
    {

        if (is_null($title))
        {
            $title = $name;
        }

        if (is_null($metaDescription))
        {
            $metaDescription = $name;
        }

        if (is_null($metaKeywords))
        {
            $metaKeywords = $name;
        }

        $this->name = $name;
        $this->title = $title;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->parameters = $parameters;
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots($metaRobots)
    {
        $this->metaRobots = $metaRobots;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->metaRobots;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!array_key_exists($name, $this->parameters))
        {
            throw new \InvalidArgumentException(sprintf('Parameter %s was not found in parameters', $name));
        }

        return $this->parameters[$name];
    }
    
}
