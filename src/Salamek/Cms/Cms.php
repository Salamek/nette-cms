<?php

namespace Salamek\Cms;

use Nette\Application\Application;
use Nette\IOException;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Salamek\Cms\Models\ILocaleRepository;
use Salamek\Cms\Models\IMenu;
use Nette\Utils\Html;
use Nette\Object;
use Salamek\Cms\Models\IMenuContent;
use Salamek\Cms\Models\IMenuContentRepository;
use Salamek\Cms\Models\IMenuRepository;
use Tracy\Debugger;

/**
 * Class Cms
 * @package Salamek\Cms
 */
class Cms extends Object
{
    /** @var string */
    private $tempPath;

    /** @var string */
    private $presenterModule;

    /** @var string */
    private $presenterMapping;

    /** @var string */
    private $layoutDir;

    /** @var string */
    private $parentClass;

    /** @var ICmsComponentRepository[] */
    private $cmsComponentRepositories = [];

    /** @var array */
    private $cmsComponentFactories;

    /** @var array */
    private $tree = [];

    /** @var array */
    private $mappings = [];

    /** @var string */
    private $defaultLayout = 'layout';

    /** @var string */
    private $cmsComponentMacroName = 'cms';

    /** @var string */
    private $defaultBlockName = 'content';

    /** @var string */
    private $presenterPrefix = 'Cms';

    /** @var IMenuRepository */
    private $menuRepository;

    /** @var IMenuContentRepository */
    private $contentRepository;

    /** @var ILocaleRepository */
    private $localeRepository;

    /** @var Application */
    private $application;

    /**
     * Cms constructor.
     * @param $tempPath
     * @param $presenterModule
     * @param $presenterMapping
     * @param $layoutDir
     * @param $parentClass
     * @param $mappings
     * @param $defaultLayout
     * @param IMenuRepository $menuRepository
     * @param IMenuContentRepository $contentRepository
     * @param ILocaleRepository $localeRepository
     * @param Application $application
     */
    public function __construct($tempPath, $presenterModule, $presenterMapping, $layoutDir, $parentClass, $mappings, $defaultLayout, IMenuRepository $menuRepository, IMenuContentRepository $contentRepository, ILocaleRepository $localeRepository, Application $application)
    {
        $this->setTempPath($tempPath);
        $this->setPresenterModule($presenterModule);
        $this->setPresenterMapping($presenterMapping);
        $this->setLayoutDir($layoutDir);
        $this->setParentClass($parentClass);
        $this->setMappings($mappings);
        $this->setDefaultLayout($defaultLayout);

        $this->menuRepository = $menuRepository;
        $this->contentRepository = $contentRepository;
        $this->localeRepository = $localeRepository;
        $this->application = $application;
    }

    /**
     * @param $tempPath
     */
    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }

    /**
     * @param $presenterModule
     */
    public function setPresenterModule($presenterModule)
    {
        $this->presenterModule = $presenterModule;
    }

    /**
     * @param $presenterMapping
     */
    public function setPresenterMapping($presenterMapping)
    {
        $this->presenterMapping = $presenterMapping;
    }

    /**
     * @param $layoutDir
     */
    public function setLayoutDir($layoutDir)
    {
        $this->layoutDir = $layoutDir;
    }

    /**
     * @param $parentClass
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }

    /**
     * @param $defaultLayout
     */
    public function setDefaultLayout($defaultLayout)
    {
        $this->defaultLayout = $defaultLayout;
    }

    /**
     * @param array $mappings
     */
    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @return mixed
     */
    public function getTempPath()
    {
        return $this->tempPath;
    }

    /**
     * @return mixed
     */
    public function getPresenterModule()
    {
        return $this->presenterModule;
    }

    /**
     * @return mixed
     */
    public function getLayoutDir()
    {
        return $this->layoutDir;
    }

    /**
     * @return mixed
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @return ICmsComponentRepository[]
     */
    public function getCmsComponentRepositories()
    {
        return $this->cmsComponentRepositories;
    }

    /**
     * @return array
     */
    public function getCmsComponentFactories()
    {
        return $this->cmsComponentFactories;
    }

    public function addComponentRepository(ICmsComponentRepository $cmsComponentRepository, $module, $component, $class)
    {
        $this->tree[$module][$component]['repository'] = [
            'object' => $cmsComponentRepository,
            'class' => $class
        ];
    }

    public function addComponent($cmsComponentFactory, $module, $component, $action, $implement)
    {
        //Check if repository exists for component
        if (array_key_exists($module, $this->tree) && array_key_exists($component, $this->tree[$module]) && array_key_exists('repository', $this->tree[$module][$component]))
        {
            $this->tree[$module][$component]['actions'][$action] = [
                'object' => $cmsComponentFactory,
                'implement' => $implement
            ];
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Repository is missing for %s\%s', $module, $component));
        }
    }

    /**
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @param $template
     * @return array
     */
    private function parseLayoutBlocks($template)
    {
        $regex = '/{block #(.+?)}(.+?|){\/block}/si';
        $matches = [];
        $matchedBlocks = [];
        if (preg_match_all($regex, $template, $matches)) {
            foreach ($matches[1] AS $k => $blockName) {
                $matchedBlocks[$blockName] = $matches[2][$k];
            }
        }

        return $matchedBlocks;
    }

    /**
     * @param $template
     * @return array
     */
    public function parsePageLayout($template)
    {
        $blocks = $this->parseLayoutBlocks($template);
        $parsedBlocks = [];
        foreach ($blocks AS $blockName => $content) {
            $parsedBlocks[$blockName] = $this->parseBlockContent($content);
        }
        return $parsedBlocks;
    }

    /**
     * @param $factory
     * @return string
     */
    private function findModuleComponentByFactory($factory)
    {
        foreach ($this->tree AS $moduleName => $components)
        {
            foreach($components AS $componentName => $actions)
            {
                foreach ($actions['actions'] AS $actionName => $action)
                {
                    if ($action['implement'] == $factory)
                    {
                        return $moduleName.'\\'.$componentName;
                    }
                }
            }
        }
    }

    /**
     * @param $block
     * @return array
     */
    private function parseBlockContent($block)
    {
        $contentArray = [];
        $dom = new \DOMDocument('1.0', 'utf-8');
        $block = mb_convert_encoding($block, 'HTML-ENTITIES', "UTF-8");
        @$dom->loadHTML($block);

        $xpathBlock = new \DOMXPath($dom);

        $rowNodes = $xpathBlock->query("//*[contains(@class, 'row')]");
        foreach ($rowNodes AS $rowNode) {
            $rowCols = [];
            foreach ($rowNode->childNodes AS $child) {
                if ($child instanceof \DOMElement) {
                    $classAttr = $child->getAttribute('class');
                    if (strpos($classAttr, 'col-') !== false) {
                        $type = null;
                        $col = null;
                        $name = null;
                        $presenter = null;
                        $action = null;

                        $classes = explode(' ', $classAttr);
                        foreach ($classes AS $class) {
                            $matches = [];
                            if (preg_match('/^col-(\S{2})-(\d{1,2})$/i', $class, $matches)) {
                                $col = $matches[2];
                                $type = $matches[1];
                                break;
                            }
                        }

                        $regexp = sprintf('/{%s\s+?(\d+)}/', $this->cmsComponentMacroName);

                        $matches = [];
                        if (preg_match($regexp, $child->nodeValue, $matches)) {
                            $menuContentId = $matches[1];
                            $menuContent = $this->contentRepository->getOneById($menuContentId);
                            
                            if ($menuContent) {
                                $rowCols[] = [
                                    'col' => $col,
                                    'type' => $type,
                                    'action' => $this->array2string(['factory' => $menuContent->getFactory(), 'parameters' => $menuContent->getParameters()]),
                                    'component' => $this->findModuleComponentByFactory($menuContent->getFactory())
                                ];
                            }
                        }
                    }
                }
            }
            $contentArray[] = $rowCols;
        }

        return $contentArray;
    }


    /**
     * @param $componentClass
     * @return array
     */
    public function getActionArray($componentClass)
    {
        list($moduleName, $componentName) = explode('\\', $componentClass);

        if (!array_key_exists($moduleName, $this->tree))
        {
            throw new \InvalidArgumentException(sprintf('Module %s not found', $moduleName));
        }

        if (!array_key_exists($componentName, $this->tree[$moduleName]))
        {
            throw new \InvalidArgumentException(sprintf('Component %s not found', $moduleName));
        }

        $response = [];
        /** @var ICmsComponentRepository $repository */
        $repository = $this->tree[$moduleName][$componentName]['repository']['object'];

        foreach($this->tree[$moduleName][$componentName]['actions'] AS $actionName => $action)
        {
            $actionOptions = $repository->getActionOptions($actionName);
            if (is_array($actionOptions) && !empty($actionOptions))
            {
                foreach ($actionOptions AS $actionOption) {
                    $response[$this->array2string(['factory' => $action['implement'], 'parameters' => $actionOption->getParameters()])] = $actionName . ': ' . $actionOption->getName();
                }
            }
            else if (is_null($actionOptions))
            {
                $response[$this->array2string(['factory' => $action['implement'], 'parameters' => []])] = $actionName;
            }
        }

        return $response;
    }

    /**
     * @param array $array
     * @return mixed
     */
    public function array2string(array $array)
    {
        return base64_encode(serialize($array));
    }

    /**
     * @param $string
     * @return mixed
     */
    public function string2array($string)
    {
        return unserialize(base64_decode($string));
    }

    /**
     * @param $mapping
     * @return string
     */
    private function buildLayoutMapping($mapping)
    {
        $templateEl = Html::el('div');
        foreach ($mapping['public'] AS $row) {
            $rowEl = Html::el('div');
            $rowEl->class = 'row';

            foreach ($row AS $col) {
                $colEl = Html::el('div');
                $colEl->class = 'col-sm-' . $col['blocks'];
                $colEl->addHtml(sprintf('{block #%s}{/block}', $col['name']));
                $rowEl->addHtml($colEl);
            }
            $templateEl->addHtml($rowEl);
        }

        return (string)$templateEl;
    }


    /**
     * @param string $template
     * @return array
     */
    public function getLayoutMapping($template = 'layout')
    {
        $return = [];

        $return['private'] = [];
        $path = sprintf($this->layoutDir . '/@%s', $template);
        $layout_content = file_get_contents($path . '.latte');
        $layout_content_map = file($path . '.map');

        $rows = [];
        foreach ($layout_content_map AS $mapLine) {
            if ($mapLine) {
                $cols = explode('|', $mapLine);
                $parsedCols = [];
                foreach ($cols AS $col) {
                    $parsedCols[] = json_decode($col, true);
                }
                $rows[] = $parsedCols;
            }
        }
        $return['public'] = $rows;

        // Check it cols are same
        $blocksInMap = [];
        foreach ($rows AS $row) {
            foreach ($row AS $col) {
                $blocksInMap[] = $col['name'];
            }
        }

        $matches = [];
        if (preg_match_all('/\{include\s+(?:|#)([a-zA-Z]+)\}/', $layout_content, $matches)) {
            foreach ($matches[1] AS $includeName) {
                if (!in_array($includeName, $blocksInMap)) {
                    $return['private'][] = $includeName;
                }
            }
        }

        return $return;
    }

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return mixed
     */
    public function saveMenuContent(IMenu $menu, $factory, array $parameters)
    {
        $menuContentFound = $this->contentRepository->getOneByMenuFactoryParameters($menu, $factory, $parameters);
        if ($menuContentFound) {
            return $menuContentFound;
        }

        return $this->contentRepository->saveMenuContent($menu, $factory, $parameters);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function detectLayouts()
    {
        $layouts = [];
        foreach (Finder::findFiles('@*.latte')->in($this->layoutDir) as $key => $file) {
            $fileName = $file->getBasename('.' . $file->getExtension());
            $mapPath = $this->layoutDir .'/'. $fileName . '.map';
            $layoutName = str_replace('@', '', $fileName);
            if (!file_exists($mapPath)) {
                throw new \Exception(sprintf('Map file %s not found for layout %s', $mapPath, $file->getBasename()));
            }
            $layouts[$layoutName] = $layoutName;
        }

        return $layouts;
    }

    /**
     * @param IMenuContent $menuContent
     * @return mixed
     */
    private function generateCmsBlockSyntax(IMenuContent $menuContent)
    {
        return sprintf('{%s %s}', $this->cmsComponentMacroName, $menuContent->getId());
    }


    /**
     * @param $string
     * @param $blockName
     * @param bool $whole
     * @return string
     */
    private function parseBlock($string, $blockName, $whole = true)
    {
        $block = sprintf('{block #%s}', $blockName);
        if ($whole) {
            $startingTag = strpos($string, $block);
            if ($startingTag === false) {
                return '';
            }
            $endTag = strpos($string, '{/block}', $startingTag);
            return substr($string, $startingTag, $endTag - $startingTag + strlen('{/block}'));
        } else {
            $startingTag = strpos($string, $block) + strlen($block);
            if ($startingTag === false) {
                return '';
            }
            $endTag = strpos($string, '{/block}', $startingTag);
            return substr($string, $startingTag, $endTag - $startingTag);
        }
    }
    
    private function generateEditableLatteTemplate(IMenu $menu, array $structure)
    {
        // Clear menu content
        $this->contentRepository->clearMenuContent($menu);
        $compiledLayout = $this->buildLayoutMapping($this->getLayoutMapping($menu->getLayoutName()));
        $lines = [];
        foreach ($structure AS $blockName => $rows) {
            $blockLines = [];
            $blockLines[] = sprintf('{block #%s}', $blockName);
            foreach ($rows AS $row) {
                $blockLines[] = '<div class="row row-editable">';
                foreach ($row AS $col) {
                    $menuContent = $this->saveMenuContent($menu, $col['action']['factory'], $col['action']['parameters']);
                    $blockLines[] = sprintf('  <div class="col-editable col-%s-%s">', $col['type'], $col['col']);
                    $blockLines[] = '    <div class="col-editable-holder">';
                    $blockLines[] = '      ' . $this->generateCmsBlockSyntax($menuContent);
                    $blockLines[] = '    </div>';
                    $blockLines[] = '  </div>';
                }
                $blockLines[] = '</div>';
            }
            $blockLines[] = '{/block}';

            $lines[] = str_replace(
                $this->parseBlock($compiledLayout, $blockName, true),
                implode("\n", $blockLines),
                $compiledLayout
            );
        }
        
        $latteTemplate = implode("\n", $lines);
        $this->menuRepository->saveLatteTemplate($menu, $latteTemplate);

        $this->generateMenuPage($menu);
    }


    /**
     * @param IMenu $menu
     * @param array $structureTree
     */
    public function saveStructureTree(IMenu $menu, array $structureTree)
    {
        $structure = [];

        foreach ($structureTree AS $blockName => $rows) {
            $newRows = [];
            foreach ($rows AS $row) {
                $newRow = [];
                foreach ($row AS $col) {
                    if ($col['action']) {
                        $newCol = [];
                        $newCol['action'] = $this->string2array($col['action']);
                        $newCol['type'] = $col['type'];
                        $newCol['col'] = $col['col'];
                        $newRow[] = $newCol;
                    }
                }

                if (count($newRow)) {
                    $newRows[] = $newRow;
                }
            }

            if (count($newRows)) {
                $structure[$blockName] = $newRows;
            }
        }

        $this->generateEditableLatteTemplate($menu, $structure);
    }

    /**
     * @return string
     */
    private function getModuleName()
    {
        $matches = [];
        if (preg_match('/\*(\S+)\\\/', $this->presenterMapping, $matches))
        {
            return $this->presenterModule.$matches[1];
        }
        elseif ($this->presenterModule)
        {
            return $this->presenterModule.'Module';
        }

        return null;
    }

    /**
     * @return string
     */
    private function getModuleNamespace()
    {
        if (preg_match('/^(\S+\*\S+)\\\/', $this->presenterMapping, $matches))
        {
            return str_replace('*', $this->presenterModule, $matches[1]).PHP_EOL;
        }
        elseif ($this->presenterModule)
        {
            return $this->presenterModule.'Module';
        }

        return null;
    }

    /**
     * @param IMenu $menu
     */
    public function generateMenuPage(IMenu $menu)
    {
        //Create namespace dir
        $presenterDir = $this->tempPath.'/'.$this->getModuleName();
        $this->mkdir($presenterDir);

        //Create templates dir
        $this->mkdir($this->tempPath.'/templates');

        //Create templates namespace dir
        $this->mkdir($this->tempPath.'/templates/'.$this->presenterModule);

        $componentList = $this->generateMenuPresenter($menu, $presenterDir);

        //Create templates presenter dir
        $templatePath = $this->tempPath.'/templates/'.$this->presenterModule.'/'.$this->presenterPrefix.$menu->getId();
        $this->mkdir($templatePath);

        $this->generateMenuTemplate($menu, $componentList, $templatePath);
    }

    /**
     * @param IMenu $menu
     * @param array $componentList
     * @param $path
     * @param string $templateName
     */
    private function generateMenuTemplate(IMenu $menu, array $componentList, $path, $templateName = 'default')
    {
        if ($componentList && $menu->getLatteTemplate())
        {
            $compiledTemplate = preg_replace_callback(
                sprintf('/{(%s)\s+?(\d+)}/', $this->cmsComponentMacroName),
                function($matches) use ($componentList){
                    return '{control '.lcfirst($componentList[$matches[2]]).'}';
                },
                $menu->getLatteTemplate());
        }
        else
        {
            $compiledTemplate = sprintf('{block #%s}', $this->defaultBlockName);
        }


        file_put_contents($path.'/'.$templateName.'.latte', $compiledTemplate);
    }

    /**
     * @param IMenu $menu
     * @param string $path
     * @return array
     */
    private function generateMenuPresenter(IMenu $menu, $path)
    {
        $presenterName = $this->presenterPrefix.$menu->getId().'Presenter';
        $class = new ClassType($presenterName);
        $class->setAbstract(false)
            ->setFinal(true)
            ->setExtends((Strings::startsWith($this->parentClass, $this->getModuleNamespace()) ? ltrim(str_replace($this->getModuleNamespace(), '', $this->parentClass), '\\') : '\\'.$this->parentClass))
            ->addTrait('\Salamek\Cms\TCmsPresenter')
            ->addComment("This is generated class, do not edit anything here, it will get overwritten!!!");

        $class->addProperty('menuId')
            ->setVisibility('public')
            ->setValue($menu->getId());
        /*
        $class->addMethod('renderDefault')
            ->setFinal(true)
            ->addBody('$this->setLayout(?);', [$menu->getLayoutName()])
            ->addBody('$this->template->metaDescription = ?;', [$menu->getMetaDescription()])
            ->addBody('$this->template->title = ?;', [$menu->getTitle()])
            ->addBody('$this->template->metaKeywords = ?;', [$menu->getMetaKeywords()])
            ->addBody('$this->template->metaRobots = ?;', [$menu->getMetaRobots()])
            ->addBody('$this->template->h1 = ?;', [$menu->getH1()])
            ->addBody('$this->template->showH1 = ?;', [$menu->isShowH1()])
            ->addBody('$this->template->bodyClass = ?;', [($menu->isHomePage() ? 'homepage': 'subpage')]);*/

        $componentList = [];
        $usedInjections = [];
        foreach ($menu->getMenuContents() AS $menuContent) {
            $propertyName = $this->classNameToVariableName($menuContent->getFactory());
            if (!in_array($propertyName, $usedInjections))
            {
                $class->addProperty($propertyName)
                    ->setVisibility('public')
                    ->addComment('@var '.(Strings::startsWith($menuContent->getFactory(), '\\') ? $menuContent->getFactory() : '\\'.$menuContent->getFactory()).' @inject');

                $usedInjections[] = $propertyName;
            }

            $componentName = ucfirst($propertyName).$menuContent->getId();
            $componentList[$menuContent->getId()] = $componentName;
            $class->addMethod('createComponent'.$componentName)
                ->setFinal(true)
                ->addBody('$cmsComponentConfiguration = new \Salamek\Cms\CmsActionOption(\'NIY\', '.var_export($menuContent->getParameters(), true).');')
                ->addBody('$control = $this->?->create($cmsComponentConfiguration);', [$propertyName])
                ->addBody('return $control;');
        }

        $filePath = $path.'/'.$presenterName.'.php';
        file_put_contents($filePath, '<?php'.PHP_EOL.'namespace '.$this->getModuleNamespace().';'.PHP_EOL.(string) $class);
        require_once ($filePath); //We need to require new presenter ASAP

        $this->menuRepository->savePresenterAction($menu, ($this->presenterModule ? ':'.$this->presenterModule.':' : '').$this->presenterPrefix.$menu->getId(), 'default');

        return $componentList;
    }

    /**
     * @return TemplateHelpers
     */
    public function createTemplateHelpers()
    {
        return new TemplateHelpers($this);
    }


    public function findComponentActionPresenter($name, array $parameters = [])
    {
        // 1) Find presenter with component created by system
        // 2) Find presenter with component created by non-system
        // 3) Create new presenter with component created as system

        // Find factory for name
        list($module, $component, $action) = explode('\\', $name);

        if (!array_key_exists($module,  $this->tree) || !array_key_exists($component,  $this->tree[$module]) || !array_key_exists($action,  $this->tree[$module][$component]['actions']))
        {
            throw new \InvalidArgumentException(sprintf('Component action %s not found in tree', $name));
        }

        $componentAction = $this->tree[$module][$component]['actions'][$action];

        /** @var ICmsComponentRepository $componentRepository */
        $componentRepository = $this->tree[$module][$component]['repository']['object'];

        //Find menu item by componentAction and parameters created by system
        $menu = $this->menuRepository->getOneByFactoryAndParametersAndIsSystem($componentAction['implement'], $parameters, true);

        if (!$menu)
        {
            $menu = $this->menuRepository->getOneByFactoryAndParametersAndIsSystem($componentAction['implement'], $parameters, false);
        }

        if (!$menu)
        {
            $componentActionInfo = $componentRepository->getActionOption($action, $parameters, $this->localeRepository->getDefault());
            $menu = $this->menuRepository->createNewMenu(
                $componentActionInfo->getName(),
                $componentActionInfo->getMetaDescription(),
                $componentActionInfo->getMetaKeywords(),
                $componentActionInfo->getMetaRobots(),
                $componentActionInfo->getTitle(),
                $componentActionInfo->getName(),
                true,
                true,
                false,
                '0.4',
                true,
                true,
                null,
                null,
                true,
                [],
                false,
                false,
                $this->defaultLayout
            );

            foreach($this->localeRepository->getActive() AS $activeLocale)
            {
                $componentActionInfoLocalized = $componentRepository->getActionOption($action, $parameters, $activeLocale);
                $this->menuRepository->translateMenu($menu, $activeLocale, $componentActionInfoLocalized->getName(), $componentActionInfoLocalized->getMetaDescription(), $componentActionInfoLocalized->getMetaKeywords(), $componentActionInfoLocalized->getTitle(), $componentActionInfoLocalized->getName());
            }
            
            $this->generateEditableLatteTemplate($menu, [ //block
                $this->defaultBlockName => [ //rows
                    [ //row
                        [ //col
                            'col' => '12',
                            'type' => 'sm',
                            'action' => [
                                'factory' => $componentAction['implement'],
                                'parameters' => $componentActionInfo->getParameters()
                            ]
                        ]
                    ]
                ]
            ]);
        }

        return $menu;
    }

    public function getLinkForMenu(IMenu $menu)
    {
        $parameters = $menu->getParameters();
        $parameters['slug'] = $menu->getSlug();
        return $this->application->getPresenter()->link($menu->getPresenter().':'.$menu->getAction(), $parameters);
    }


    /**
     * @param string $dir
     * @throws IOException
     * @return void
     */
    private static function mkdir($dir)
    {
        $oldMask = umask(0);
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777);
        umask($oldMask);

        if (!is_dir($dir) || !is_writable($dir)) {
            throw new IOException("Please create writable directory $dir.");
        }
    }

    /**
     * @param $className
     * @return mixed
     */
    private function classNameToVariableName($className)
    {
        return lcfirst(implode('', explode('\\', $className)));
    }
}
