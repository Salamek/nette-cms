<?php

namespace Salamek\Cms;


/**
 * Class TemplatedEmail
 * @package Salamek\TemplatedEmail
 */
class Cms extends Nette\Object
{
    private $tempPath;

    private $presenterNamespace;


    public function __construct($tempPath, $presenterNamespace)
    {
        $this->setTempPath($tempPath);
        $this->setPresenterNamespace($presenterNamespace);
    }


    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }


    public function setPresenterNamespace($presenterNamespace)
    {
        $this->presenterNamespace = $presenterNamespace;
    }

}
