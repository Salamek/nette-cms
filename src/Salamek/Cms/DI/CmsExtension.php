<?php

namespace Salamek\Gitlab\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;

/**
 * Class CmsExtension
 * @package Salamek\Cms\DI
 */
class CmsExtension extends Nette\DI\CompilerExtension
{

    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('cms'))
            ->setClass('Salamek\Cms\Cms', [$config['tempPath'], $config['presenterNamespace']])
            ->addSetup('setTempPath', [$config['tempPath']])
            ->addSetup('setPresenterNamespace', [$config['presenterNamespace']]);
    }


    /**
     * @param Configurator $config
     * @param string $extensionName
     */
    public static function register(Configurator $config, $extensionName = 'templatedEmailExtension')
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
            $compiler->addExtension($extensionName, new CmsExtension());
        };
    }


    /**
     * {@inheritdoc}
     */
    public function getConfig(array $defaults = [], $expand = true)
    {
        $defaults = [
            'tempPath' => $this->getContainerBuilder()->parameters['tempDir'] . '/cms',
            'presenterNamespace' => 'FrontModule'
        ];

        return parent::getConfig($defaults, $expand);
    }
}
