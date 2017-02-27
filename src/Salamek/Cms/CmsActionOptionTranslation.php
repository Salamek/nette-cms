<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 27.2.17
 * Time: 6:45
 */

namespace Salamek\Cms;


use Salamek\Cms\Models\ILocale;

class CmsActionOptionTranslation implements ICmsActionOptionTranslation
{
    /** @var ILocale */
    private $locale;

    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var string */
    private $metaDescription;

    /** @var string */
    private $metaKeywords;

    /**
     * CmsActionOptionTranslation constructor.
     * @param $locale
     * @param $name
     * @param $title
     * @param $metaDescription
     * @param $metaKeywords
     */
    public function __construct(ILocale $locale, $name, $title, $metaDescription, $metaKeywords)
    {
        $this->locale = $locale;
        $this->name = $name;
        $this->title = $title;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
    }


    /**
     * @param ILocale $locale
     */
    public function setLocale(ILocale $locale)
    {
        $this->locale = $locale;
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return mixed
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

}