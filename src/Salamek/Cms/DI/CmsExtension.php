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
            ->setClass('Salamek\Cms\Cms', [$config['tempPath'], $config['presenterModule'], $config['presenterMapping'], $config['layoutDir'], $config['parentClass'], $config['mappings'], $config['defaultLayout']])
            ->addSetup('setTempPath', [$config['tempPath']])
            ->addSetup('setPresenterModule', [$config['presenterModule']])
            ->addSetup('setPresenterMapping', [$config['presenterMapping']])
            ->addSetup('setLayoutDir', [$config['layoutDir']])
            ->addSetup('setParentClass', [$config['parentClass']])
            ->addSetup('setDefaultLayout', [$config['defaultLayout']])
            ->addSetup('setMappings', [$config['mappings']])
            ->addSetup('setTemplateOverrides', [$config['templateOverrides']]);

        $builder->addDefinition($this->prefix('helpers'))
            ->setClass('Salamek\Cms\TemplateHelpers')
            ->setFactory($this->prefix('@cms') . '::createTemplateHelpers')
            ->setInject(FALSE);

        $this->loadConsole();
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

    /**
     * @param $class
     * @return array|null
     */
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

    /**
     * @param $class
     * @return array|null
     */
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

    /**
     * @param $mapping
     * @return string
     */
    private function mappingToRegexp($mapping)
    {
        if (!Strings::contains($mapping, '*'))
        {
            throw new \InvalidArgumentException(sprintf('There are no wildcards in mapping %s', $mapping));
        }

        $mapping = preg_quote($mapping, '/');

        $replaceWildcard = '\*';
        $wildcardsReplaces = [
            '(?P<module>[^\\\\\\\\]*?)',
            '(?P<component>[^\\\\\\\\]*?)',
            '(?P<action>[^\\\\\\\\]*?)'
        ];

        $occurrence = substr_count($mapping, $replaceWildcard);
        for ($i=0; $i < $occurrence; $i++)
        {
            $from = '/'.preg_quote($replaceWildcard, '/').'/';
            $mapping = preg_replace($from, $wildcardsReplaces[$i], $mapping, 1);
        }

        $mapping = preg_replace('/'.preg_quote('\-', '/').'/', '([^\\\\\\\\]*?)', $mapping);

        return '/^'.$mapping.'$/i';
    }

    /**
     * @param $mapping
     * @param $class
     * @return array|null
     */
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


        $builder = $this->getContainerBuilder();
        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {
            $def->addSetup('?->onCompile[] = function($engine) { Salamek\Cms\Macros\Latte::install($engine->getCompiler()); }', ['@self']);

            if (method_exists('Latte\Engine', 'addProvider')) { // Nette 2.4
                $def->addSetup('addProvider', ['cms', $this->prefix('@cms')])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLinkFilterAware']]);
            } else {
                $def->addSetup('addFilter', ['getCms', [$this->prefix('@helpers'), 'getCms']])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLink']]);
            }
        };

        $latteFactoryService = $builder->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory');
        if (!$latteFactoryService || !self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\engine')) {
            $latteFactoryService = 'nette.latteFactory';
        }

        if ($builder->hasDefinition($latteFactoryService) && self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\Engine')) {
            $registerToLatte($builder->getDefinition($latteFactoryService));
        }

        if ($builder->hasDefinition('nette.latte')) {
            $registerToLatte($builder->getDefinition('nette.latte'));
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
            'presenterModule' => 'Front',
            'presenterMapping' => '*Module\*Presenter',
            'layoutDir' => $this->getContainerBuilder()->parameters['appDir'] . '/FrontModule/templates',
            'defaultLayout' => 'layout',
            'parentClass' => 'CmsPresenter',
            'mappings' => [],
            'templateOverrides' => []
        ];

        return parent::getConfig($defaults, $expand);
    }

    /**
     * @param string $class
     * @param string $type
     * @return bool
     */
    private static function isOfType($class, $type)
    {
        return $class === $type || is_subclass_of($class, $type);
    }
}
