<?php
namespace Fluid\Page;

use Countable;
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
     * @var StorageInterface
     * @deprecated Use registry
     */
    private $storage;

    /**
     * @var XmlMappingLoaderInterface
     * @deprecated Use registry
     */
    private $xmlMappingLoader;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PageMapper
     */
    private $pageMapper;

    /**
     * @var PageConfig
     */
    private $config;

    /**
     * @var LanguageEntity
     */
    private $language;

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
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param PageMapper $pageMapper
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, PageMapper $pageMapper, LanguageEntity $language)
    {
        $this->setRegistry($registry);
        $this->setStorage($storage);
        $this->setXmlMappingLoader($xmlMappingLoader);
        $this->setPageMapper($pageMapper);
        $this->setConfig(new PageConfig($this));
        $this->setLanguage($language);
        $this->setPages(new PageCollection($this->getRegistry(), $storage, $xmlMappingLoader, $pageMapper, $this->getLanguage(), $this));
        $this->setVariables(new VariableCollection($this, $storage, $xmlMappingLoader, null, $this->getLanguage()));
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->getRegistry()->getData();
        $data->setMap($this->getRegistry()->getMap());
        $data->setRequest($this->getRegistry()->getRouter()->getRequest());
        return $data->getPageData($this);
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
        $template = new TemplateEntity($this->getConfig()->getTemplate(), $this->variables, $this->getXmlMappingLoader());
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
     * @param PageMapper $pageMapper
     * @return $this
     */
    public function setPageMapper(PageMapper $pageMapper)
    {
        $this->pageMapper = $pageMapper;
        return $this;
    }

    /**
     * @return PageMapper
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
     * @param StorageInterface $storage
     * @return $this
     * @deprecated Use registry
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated Use registry
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @return $this
     * @deprecated Use registry
     */
    public function setXmlMappingLoader(XmlMappingLoaderInterface $xmlMappingLoader)
    {
        $this->xmlMappingLoader = $xmlMappingLoader;
        return $this;
    }

    /**
     * @return XmlMappingLoaderInterface
     * @deprecated Use registry
     */
    public function getXmlMappingLoader()
    {
        return $this->xmlMappingLoader;
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
     * @param LanguageEntity $language
     * @return $this
     */
    public function setLanguage(LanguageEntity $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntity
     */
    public function getLanguage()
    {
        return $this->language;
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
        return $this->offsetExists($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        $array = ['name', 'language', 'pages', 'url', 'template', 'languages', 'allow_childs', 'child_templates'];
        return $this->getVariables()->count() + count($array);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $array = array_merge(
            [
                'name' => $this->getName(),
                'language' => $this->getLanguage()->getLanguage(),
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
     */
    public function offsetExists($offset)
    {
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
     */
    public function offsetGet($offset)
    {
        if ($offset === 'name') {
            return $this->getName();
        } elseif ($offset === 'language') {
            return $this->getLanguage()->getLanguage();
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
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === 'name') {
            $this->setName($value);
        } elseif ($offset === 'language') {
            $this->getLanguage()->setLanguage($value);
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
     */
    public function offsetUnset($offset)
    {
        if ($offset === 'name') {
            $this->setName(null);
        } elseif ($offset === 'pages') {
            $this->setPages(null);
        } elseif ($offset === 'language') {
            $this->getLanguage()->setLanguage(null);
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