<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Cms;


trait TCms
{

    /** @var Cms */
    public $cms;
    
    /**
     * @param Cms $cms
     */
    public function injectCms(Cms $cms)
    {
        $this->cms = $cms;
    }
}