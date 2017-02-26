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
    private $metaRobots = 'index, follow';

    /** @var array */
    private $parameters = [];

    /** @var ICmsActionOptionTranslation[] */
    private $translations = [];

    /**
     * CmsActionOption constructor.
     * @param array $parameters
     * @param string $metaRobots
     */
    public function __construct(array $parameters = [], $metaRobots = 'index, follow')
    {
        $this->parameters = $parameters;
        $this->metaRobots = $metaRobots;
    }


    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
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

    /**
     * @param ICmsActionOptionTranslation $cmsActionOptionTranslation
     */
    public function addTranslation(ICmsActionOptionTranslation $cmsActionOptionTranslation)
    {
        $this->translations[] = $cmsActionOptionTranslation;
    }

    /**
     * @return ICmsActionOptionTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
