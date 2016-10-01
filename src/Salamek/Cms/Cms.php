<?php

namespace Salamek\Cms;

use Nette\IOException;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Salamek\Cms\Models\IMenu;
use Nette\Utils\Html;
use Nette\Object;
use Salamek\Cms\Models\IMenuContent;
use Salamek\Cms\Models\IMenuContentRepository;
use Salamek\Cms\Models\IMenuRepository;
use Tracy\Debugger;

/**
 * Class TemplatedEmail
 * @package Salamek\TemplatedEmail
 */
class Cms extends Object
{
    private $tempPath;

    private $presenterNamespace;

    private $layoutDir;

    private $parentClass;

    /** @var ICmsComponentRepository[] */
    private $cmsComponentRepositories = [];

    /** @var array */
    private $cmsComponentFactories;

    /** @var array */
    private $tree = [];

    /** @var array */
    private $mappings = [];

    /**
     * @var string
     */
    private $cmsComponentMacroName = 'cms';

    private $defaultBlockName = 'content';

    /** @var IMenuRepository */
    private $menuRepository;

    /** @var IMenuContentRepository */
    private $contentRepository;
    
    public function __construct($tempPath, $presenterNamespace, $layoutDir, $parentClass, $mappings, IMenuRepository $menuRepository, IMenuContentRepository $contentRepository)
    {
        $this->setTempPath($tempPath);
        $this->setPresenterNamespace($presenterNamespace);
        $this->setLayoutDir($layoutDir);
        $this->setParentClass($parentClass);
        $this->setMappings($mappings);

        $this->menuRepository = $menuRepository;
        $this->contentRepository = $contentRepository;
    }


    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }

    public function addComponentRepository(ICmsComponentRepository $cmsComponentRepository, $module, $component, $class)
    {
        $this->addRepositoryToTree($cmsComponentRepository, $module, $component, $class);
    }

    public function addComponent($cmsComponentFactory, $module, $component, $action, $class)
    {
        $this->addComponentToTree($cmsComponentFactory, $module, $component, $action, $class);
    }

    public function setPresenterNamespace($presenterNamespace)
    {
        $this->presenterNamespace = $presenterNamespace;
    }

    public function setLayoutDir($layoutDir)
    {
        $this->layoutDir = $layoutDir;
    }

    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
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
    public function getPresenterNamespace()
    {
        return $this->presenterNamespace;
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

    private function addRepositoryToTree(ICmsComponentRepository $cmsComponentRepository, $module, $component, $class)
    {
        $this->tree[$module][$component]['repository'] = [
            'object' => $cmsComponentRepository,
            'class' => $class
        ];
    }

    private function addComponentToTree($cmsComponentFactory, $module, $component, $action, $implement)
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
        $menuContentFound = $this->contentRepository->getByOneByMenuFactoryParameters($menu, $factory, $parameters);
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
     * @param IMenu $menu
     */
    public function generateMenuPage(IMenu $menu)
    {
        //Create namespace dir
        $presenterDir = $this->tempPath.'/'.$this->presenterNamespace;
        $this->mkdir($presenterDir);

        //Create templates dir
        $this->mkdir($this->tempPath.'/templates');

        //Create templates namespace dir
        $this->mkdir($this->tempPath.'/templates/'.str_replace('Module', '', $this->presenterNamespace));

        //Create templates presenter dir
        $templatePath = $this->tempPath.'/templates/'.str_replace('Module', '', $this->presenterNamespace).'/'.$this->dashesToCamelCase($menu->getSlug());
        $this->mkdir($templatePath);

        $componentList = $this->generateMenuPresenter($menu, $presenterDir);

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
        $presenterName = $this->dashesToCamelCase($menu->getSlug()).'Presenter';
        $class = new ClassType($presenterName);
        $class->setAbstract(false)
            ->setFinal(true)
            ->setExtends((Strings::startsWith($this->parentClass, $this->presenterNamespace) ? ltrim(str_replace($this->presenterNamespace, '', $this->parentClass), '\\') : '\\'.$this->parentClass))
            ->addTrait('\Salamek\Cms\TCmsPresenter')
            ->addComment("This is generated class, do not edit anything here, it will get overwritten!!!");

        $class->addMethod('renderDefault')
            ->setFinal(true)
            ->addBody('$this->setLayout(?);', [$menu->getLayoutName()])
            ->addBody('$this->template->metaDescription = ?;', [$menu->getMetaDescription()])
            ->addBody('$this->template->title = ?;', [$menu->getTitle()])
            ->addBody('$this->template->metaKeywords = ?;', [$menu->getMetaKeywords()])
            ->addBody('$this->template->metaRobots = ?;', [$menu->getMetaRobots()])
            ->addBody('$this->template->h1 = ?;', [$menu->getH1()])
            ->addBody('$this->template->showH1 = ?;', [$menu->isShowH1()])
            ->addBody('$this->template->bodyClass = ?;', [($menu->isHomePage() ? 'homepage': 'subpage')]);

        $componentList = [];
        $usedInjections = [];
        foreach ($menu->getMenuContents() AS $menuContent) {
            $propertyName = $this->classNameToVariableName($menuContent->getFactory());
            if (!in_array($propertyName, $usedInjections))
            {
                $class->addProperty($propertyName)
                    ->setVisibility('public')
                    ->addComment('@var '.(Strings::startsWith($menuContent->getFactory(), '\\') ? $menuContent->getFactory() : '\\'.$menuContent->getFactory()).' @inject');

                $usedInjections[] = $usedInjections;
            }

            $componentName = ucfirst($propertyName).$menuContent->getId();
            $componentList[$menuContent->getId()] = $componentName;
            $class->addMethod('createComponent'.$componentName)
                ->setFinal(true)
                ->addBody('$cmsComponentConfiguration = new \Salamek\Cms\CmsActionOption(\'NIY\', '.var_export($menuContent->getParameters(), true).');')
                ->addBody('$control = $this->?->create($cmsComponentConfiguration);', [$propertyName])
                ->addBody('return $control;');
        }

        file_put_contents($path.'/'.$presenterName.'.php', '<?php'.PHP_EOL.'namespace '.$this->presenterNamespace.';'.PHP_EOL.(string) $class);

        $this->menuRepository->savePresenterAction($menu, $this->dashesToCamelCase($menu->getSlug()), 'default');

        return $componentList;
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
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed
     */
    private function dashesToCamelCase($string, $capitalizeFirstCharacter = true)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
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
