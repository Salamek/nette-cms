<?php

namespace Salamek\Cms\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;
use Tracy\Debugger;
use Nette\Utils\Strings;

/**
 * Class CmsExtension
 * @package Salamek\Cms\DI
 */
class CmsExtension extends Nette\DI\CompilerExtension
{
    //const TAG_REPOSITORY = 'salamek.cms.repository';
    const TAG_COMPONENT = 'salamek.cms.component';


    public function loadConfiguration()
    {
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('cms'))
            ->setClass('Salamek\Cms\Cms', [$config['tempPath'], $config['presenterNamespace'], $config['layoutDir'], $config['parentClass'], $config['mappings']])
            ->addSetup('setTempPath', [$config['tempPath']])
            ->addSetup('setPresenterNamespace', [$config['presenterNamespace']])
            ->addSetup('setLayoutDir', [$config['layoutDir']])
            ->addSetup('setParentClass', [$config['parentClass']])
            ->addSetup('setMappings', [$config['mappings']]);


        $this->loadConsole();

        /*$builder->getDefinition($builder->getByType('Nette\Application\IPresenterFactory') ?: 'nette.presenterFactory')
            ->addSetup('if (method_exists($service, ?)) { $service->setMapping([? => ?]); } ' .
                'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }', [
                'setMapping', 'Kdyby', 'KdybyModule\*\*Presenter', 'mapping', 'Kdyby', 'KdybyModule\*\*Presenter'
            ]);*/
    }

    protected function loadConsole()
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->addTag(ConsoleExtension::TAG_COMMAND)
                ->setInject(FALSE); // lazy injects

            if (is_string($command)) {
                $cli->setClass($command);

            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    private function findRepositoryMapping($class)
    {
        $config = $this->getConfig();
        foreach($config['mappings'] AS $mappingComponent => $mappingRepository)
        {
            $match = $this->matchMapping($mappingRepository, $class);
            if ($match)
            {
                return $match;
            }
        }
        return null;
    }

    private function findComponentMapping($class)
    {
        $config = $this->getConfig();
        foreach($config['mappings'] AS $mappingComponent => $mappingRepository)
        {
            $match = $this->matchMapping($mappingComponent, $class);
            if ($match)
            {
                return $match;
            }
        }
        return null;
    }

    private function mappingToRegexp($mapping)
    {
        if (!Strings::contains($mapping, '*'))
        {
            throw new \InvalidArgumentException(sprintf('There are no wildcards in mapping %s', $mapping));
        }

        $mapping = preg_quote($mapping, '/');

        $replaceWildcard = '\*';
        $wildcardsReplaces = [
            '(?P<module>\S+)',
            '(?P<component>\S+)',
            '(?P<action>\S+)'
        ];

        $occurrence = substr_count($mapping, $replaceWildcard);
        for ($i=0; $i < $occurrence; $i++)
        {
            $from = '/'.preg_quote($replaceWildcard, '/').'/';
            $mapping = preg_replace($from, $wildcardsReplaces[$i], $mapping, 1);
        }

        return '/^'.$mapping.'$/i';
    }


    private function matchMapping($mapping, $class)
    {
        $regexp = $this->mappingToRegexp($mapping);
        $matches = [];
        if (preg_match($regexp, $class, $matches))
        {
            return [$matches['module'], $matches['component'], (array_key_exists('action', $matches) ? $matches['action'] : null)];
        }

        return null;
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $cms = $builder->getDefinition($this->prefix('cms'));
        

        foreach ($builder->findByType('Salamek\Cms\ICmsComponentRepository') AS $serviceName => $service) {
            $match = $this->findRepositoryMapping($service->getClass());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $cms->addSetup('addComponentRepository', ['@' . $serviceName, $module, $component, $service->getClass()]);
            }
        }
        
        foreach ($builder->findByTag(self::TAG_COMPONENT) AS $serviceName => $bool) {
            $service = $builder->getDefinition($serviceName);
            $match = $this->findComponentMapping($service->getImplement());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $cms->addSetup('addComponent', ['@' . $serviceName, $module, $component, $action, $service->getImplement()]);
            }
        }
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
            'presenterNamespace' => 'FrontModule',
            'layoutDir' => $this->getContainerBuilder()->parameters['appDir'] . '/FrontModule/templates',
            'parentClass' => 'CmsPresenter',
            'mappings' => []
        ];

        return parent::getConfig($defaults, $expand);
    }
}
