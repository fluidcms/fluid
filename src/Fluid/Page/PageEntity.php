<?php
namespace Fluid\Page;

use Countable;
use Fluid\Data\DataCollection;
use JsonSerializable;
use Fluid\Language\LanguageEntity;
use Fluid\Page\Renderer\RenderPage;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Fluid\Variable\VariableCollection;
use Fluid\StorageInterface;
use Fluid\XmlMappingLoaderInterface;
use Fluid\Template\TemplateEntity;
use Fluid\Page\Renderer\RendererInterface;
use Fluid\RegistryInterface;

class PageEntity implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    const GLOBAL_PAGE = 'global';

    /**
     * @var string
     */
    private $name;

    /**
     * @var PageCollection
     */
    private $pages;

    /**
     * @var TemplateEntity
     */
    private $template;

    /**
     * @var VariableCollection
     */
    private $variables;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var bool
     */
    private $isMapped = false;

    /**
     * @var PageMapper
     * @deprecated
     */
    private $pageMapper;

    /**
     * @var PageConfig
     */
    private $config;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var PageEntity
     */
    private $parent;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->setPageMapper($registry->getPageMapper());
        $this->setConfig(new PageConfig($this));
        $this->setPages(new PageCollection($registry, $this));
        $this->setVariables(new VariableCollection($this->registry, $this));
    }

    /**
     * @return DataCollection
     */
    public function getData()
    {
        $data = $this->getRegistry()->getDataMapper();
        $data->setMap($this->getRegistry()->getMap());
        $data->setRequest($this->getRegistry()->getRouter()->getRequest());
        return $data->mapPage($this);
    }

    /**
     * @return array
     * @deprecated use jsonSerialize instead
     */
    public function toArray()
    {
        return $this->jsonSerialize();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'config' => $this->getConfig()->toArray(),
            'template' => $this->getTemplate()->toArray(),
            'variables' => $this->getVariables()->toArray()
        ];
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     * @return $this
     */
    public function set($attributes, $value = null)
    {
        $config = $this->getConfig();

        if (is_string($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'name') {
                $this->setName($value);
            } elseif ($key === 'pages' && $config->allowChilds()) {
                $this->getPages()->addPages($value);
            } elseif ($key === 'variables') {
                $this->getVariables()->addVariables($value);
            } elseif ($key === 'url' && empty($config->getUrl())) {
                $config->setUrl($value);
            } elseif ($key === 'template' && empty($config->getTemplate())) {
                $this->getConfig()->setTemplate($value);
            } elseif ($key === 'languages' && empty($config->getLanguages())) {
                $this->getConfig()->setLanguages($value);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (null === $this->renderer) {
            $this->renderer = new RenderPage($this->getRegistry(), $this);
        }
        return $this->renderer->render();
    }

    /**
     * @return array
     */
    public function getParents()
    {
        $parents = [];
        $page = $this;
        while ($page->getParent()) {
            $parents[] = $page->getParent();
            $page = $page->getParent();
        }
        return $parents;
    }

    /**
     * @return bool
     */
    public function hasPages()
    {
        return $this->getPages()->count() !== 0;
    }

    /**
     * @return bool
     */
    public function isGlobalPage()
    {
        return $this->getName() === self::GLOBAL_PAGE;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getId()
    {
        return $this->name;
    }

    /**
     * @param TemplateEntity $template
     * @return $this
     */
    public function setTemplate(TemplateEntity $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        if (null === $this->template) {
            $this->createTemplate();
        }
        return $this->template;
    }

    /**
     * @return $this
     */
    private function createTemplate()
    {
        $template = new TemplateEntity($this->registry, $this->getConfig()->getTemplate(), $this->variables);
        $this->setTemplate($template);
        if (!$this->variables->isMapped()) {
            $this->variables->mapCollection();
        }
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->getPages()->setPath($name);
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param PageCollection $pages
     * @return $this
     */
    public function setPages(PageCollection $pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return PageCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param VariableCollection $variables
     * @return $this
     */
    public function setVariables(VariableCollection $variables)
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return VariableCollection
     */
    public function getVariables()
    {
        if (!$this->variables->isMapped()) {
            $this->variables->mapCollection();
        }
        return $this->variables;
    }

    /**
     * @return bool
     */
    public function isMapped()
    {
        return $this->isMapped;
    }

    /**
     * @param bool $isMapped
     * @return $this
     */
    public function setIsMapped($isMapped)
    {
        $this->isMapped = $isMapped;
        return $this;
    }

    /**
     * @param PageMapper $pageMapper
     * @return $this
     * @deprecated
     */
    public function setPageMapper(PageMapper $pageMapper)
    {
        $this->pageMapper = $pageMapper;
        return $this;
    }

    /**
     * @return PageMapper
     * @deprecated
     */
    public function getPageMapper()
    {
        return $this->pageMapper;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * @param PageConfig $config
     * @return $this
     */
    public function setConfig(PageConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return PageConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return PageEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param PageEntity $parent
     * @return $this
     */
    public function setParent(PageEntity $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }
        return $this->offsetGet($name);
    }

    /**
     * @return int
     * todo: make sure these are not provided by the data object
     */
    public function count()
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        $array = ['name', 'language', 'pages', 'url', 'template', 'languages', 'allow_childs', 'child_templates'];
        return $this->getVariables()->count() + count($array);
    }

    /**
     * @return ArrayIterator
     * todo: make sure these are not provided by the data object
     */
    public function getIterator()
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        $array = array_merge(
            [
                'name' => $this->getName(),
                'language' => $this->registry->getLanguage()->getLanguage(),
                'pages' => $this->getPages()->getIterator(),
                'url' => $this->getConfig()->getUrl(),
                'template' => $this->getConfig()->getTemplate(),
                'languages' => $this->getConfig()->getLanguages(),
                'allow_childs' => $this->getConfig()->getAllowChilds(),
                'child_templates' => $this->getConfig()->getChildTemplates(),
            ],
            $this->getVariables()->getIterator()
        );
        return new ArrayIterator($array);
    }

    /**
     * @param int $offset
     * @return bool
     * todo: make sure these are not provided by the data object
     */
    public function offsetExists($offset)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        if ($offset === 'name' || $offset === 'language' || $offset === 'pages' || $offset === 'url' || $offset === 'template' ||
            $offset === 'languages' || $offset === 'allow_childs' || $offset === 'child_templates'
        ) {
            return true;
        }
        return isset($this->getVariables()[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     * todo: make sure these are not provided by the data object
     */
    public function offsetGet($offset)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        if ($offset === 'name') {
            return $this->getName();
        } elseif ($offset === 'language') {
            return $this->registry->getLanguage()->getLanguage();
        } elseif ($offset === 'pages') {
            return $this->getPages();
        } elseif ($offset === 'url') {
            return $this->getConfig()->getUrl();
        } elseif ($offset === 'template') {
            return $this->getConfig()->getTemplate();
        } elseif ($offset === 'languages') {
            return $this->getConfig()->getLanguages();
        } elseif ($offset === 'allow_childs') {
            return $this->getConfig()->getAllowChilds();
        } elseif ($offset === 'child_templates') {
            return $this->getConfig()->getChildTemplates();
        } else {
            return $this->getVariables()[$offset];
        }
    }

    /**
     * @param int $offset
     * @param mixed $value
     * todo: make sure these are not provided by the data object
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        if ($offset === 'name') {
            $this->setName($value);
        } elseif ($offset === 'language') {
            $this->registry->getLanguage()->setLanguage($value);
        } elseif ($offset === 'pages') {
            $this->setPages($value);
        } elseif ($offset === 'url') {
            $this->getConfig()->setUrl($value);
        } elseif ($offset === 'template') {
            $this->getConfig()->setTemplate($value);
        } elseif ($offset === 'languages') {
            $this->getConfig()->setLanguages($value);
        } elseif ($offset === 'allow_childs') {
            $this->getConfig()->setAllowChilds($value);
        } elseif ($offset === 'child_templates') {
            $this->getConfig()->setChildTemplates($value);
        } else {
            $this->getVariables()[$offset] = $value;
        }
    }

    /**
     * @param int $offset
     * todo: make sure these are not provided by the data object
     */
    public function offsetUnset($offset)
    {
        if (!$this->isMapped()) {
            $this->registry->getPageMapper()->mapJsonObject($this);
        }

        if ($offset === 'name') {
            $this->setName(null);
        } elseif ($offset === 'pages') {
            $this->setPages(null);
        } elseif ($offset === 'language') {
            $this->registry->getLanguage()->setLanguage(null);
        } elseif ($offset === 'url') {
            $this->getConfig()->setUrl(null);
        } elseif ($offset === 'template') {
            $this->getConfig()->setTemplate(null);
        } elseif ($offset === 'languages') {
            $this->getConfig()->setLanguages(null);
        } elseif ($offset === 'allow_childs') {
            $this->getConfig()->setAllowChilds(null);
        } elseif ($offset === 'child_templates') {
            $this->getConfig()->setChildTemplates(null);
        } else {
            unset($this->getVariables()[$offset]);
        }
    }
}