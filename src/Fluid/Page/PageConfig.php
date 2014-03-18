<?php
namespace Fluid\Page;

class PageConfig
{
    /**
     * @var PageEntity
     */
    private $page;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $allowChilds;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $childTemplates;

    /**
     * @var array
     */
    private $languages;

    /**
     * @param PageEntity $page
     */
    public function __construct(PageEntity $page)
    {
        $this->setPage($page);
    }

    /**
     * @param array|string $attributes
     * @param mixed|null $value
     */
    public function set($attributes, $value = null)
    {
        if (is_string($attributes)) {
            $attributes = [$attributes => $value];
        }

        foreach ($attributes as $key => $value) {
            if ($key === 'template') {
                $this->setTemplate($value);
            } elseif ($key === 'allow-childs') {
                $this->setAllowChilds($value);
            } elseif ($key === 'url') {
                $this->setUrl($value);
            } elseif ($key === 'child-templates') {
                $this->setChildTemplates($value);
            } elseif ($key === 'languages') {
                $this->setLanguages($value);
            }
        }
    }

    /**
     * @param PageEntity $page
     * @return $this
     */
    public function setPage(PageEntity $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return PageEntity
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param bool $allowChilds
     * @return $this
     */
    public function setAllowChilds($allowChilds)
    {
        if ($allowChilds === 'false' || $allowChilds === '0') {
            $allowChilds = false;
        } elseif ($allowChilds === 'true' || $allowChilds === '1') {
            $allowChilds = true;
        }
        $this->allowChilds = (bool)$allowChilds;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowChilds()
    {
        return $this->allowChilds;
    }

    /**
     * @return boolean
     */
    public function allowChilds()
    {
        return $this->getAllowChilds();
    }

    /**
     * @param array $childTemplates
     * @return $this
     */
    public function setChildTemplates($childTemplates)
    {
        if (is_string($childTemplates)) {
            $childTemplates = explode(',', $childTemplates);
            foreach ($childTemplates as $key => $value) {
                $childTemplates[$key] = trim($value);
            }
        }
        $this->childTemplates = $childTemplates;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildTemplates()
    {
        return $this->childTemplates;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages)
    {
        if (is_string($languages)) {
            $languages = explode(',', $languages);
            foreach ($languages as $key => $value) {
                $languages[$key] = trim($value);
            }
        }
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }
}