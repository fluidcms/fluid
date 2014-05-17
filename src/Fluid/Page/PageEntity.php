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
        $this->setPages(new PageCollection($this->getRegistry(), $storage, $xmlMappingLoader, $pageMapper, $this->getLanguage()));
        $this->setVariables(new VariableCollection($this, $storage, $xmlMappingLoader, null, $this->getLanguage()));
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'page' => $this,
            'map' => $this->getRegistry()->getMap()
        ];
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
     * @return string
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
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if ($name === 'pages') {
            return true;
        }
        return $this->getVariables()->__isset($name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if ($name === 'pages') {
            return $this->getPages();
        }
        return $this->getVariables()->__call($name, $arguments);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'pages') {
            return $this->getPages();
        }
        return $this->getVariables()->__get($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->getVariables()->count() + 1;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $array = array_merge(
            ['pages' => $this->getPages()->getIterator()],
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
        if ($offset === 'pages') {
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
        if ($offset === 'pages') {
            return $this->getPages();
        }
        return $this->getVariables()[$offset];
    }

    /**
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === 'pages') {
            $this->setPages($value);
            return;
        }
        $this->getVariables()[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        if ($offset === 'pages') {
            $this->setPages(null);
            return;
        }
        unset($this->getVariables()[$offset]);
    }
}