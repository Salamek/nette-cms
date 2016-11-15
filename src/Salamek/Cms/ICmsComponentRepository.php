<?php
namespace Salamek\Cms;

use Salamek\Cms\Models\ILocale;


/**
 * Description of iCmsComponentRepository
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsComponentRepository
{
    /**
     * @param string $componentAction
     * @return ICmsActionOption[]|false|null
     */
    public function getActionOptions($componentAction);

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return ICmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale);
}
