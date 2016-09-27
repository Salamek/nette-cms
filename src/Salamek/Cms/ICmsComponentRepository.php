<?php
namespace Salamek\Cms;


/**
 * Description of iCmsComponentRepository
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsComponentRepository
{
    /**
     * @return array
     */
    public function getActions();

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction);

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return ICmsActionOption
     */
    public function getActionOption($componentAction, array $parameters);
}
