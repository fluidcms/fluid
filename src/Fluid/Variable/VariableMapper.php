<?php
namespace Fluid\Variable;

use Fluid\Language\LanguageEntity;
use Fluid\Page\PageEntity;
use Fluid\RegistryInterface;
use Fluid\StorageInterface;

class VariableMapper
{
    const DATA_DIRECTORY = 'pages';

    /**
     * @var LanguageEntity
     */
    private $language;

    /**
     * @var StorageInterface
     * @deprecated
     */
    private $storage;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     * @param LanguageEntity $language
     */
    public function __construct(RegistryInterface $registry, LanguageEntity $language = null)
    {
        $this->registry = $registry;
        $this->setStorage($registry->getStorage());
        if (null !== $language) {
            $this->setLanguage($language);
        }
    }

    /**
     * @param VariableCollection $collection
     */
    public function persist(VariableCollection $collection)
    {
        $file = $this->getFile($collection->getPage(), $this->getLanguage()->getLanguage());
        $this->getStorage()->saveBranchData($file, $collection->toArray());
    }

    /**
     * @param VariableCollection $collection
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        $file = $this->getFile($collection->getPage(), $this->getLanguage()->getLanguage());
        $data = $this->getStorage()->loadBranchData($file);

        if (is_array($data)) {
            foreach ($data as $item) {
                $variable = $variables->find($item['name']);
                if ($variable) {
                    if (isset($item['value'])) {
                        $variable->setValue($item['value']);
                    } elseif (isset($item['variables'])) {
                        $variable->reset($item['variables']);
                    }
                }
            }
        }
    }

    /**
     * @param PageEntity $page
     * @param string $language
     * @return string
     */
    private function getFile(PageEntity $page, $language)
    {
        $filepath = '';
        $parent = $page->getParent();
        while ($parent) {
            $filepath .= DIRECTORY_SEPARATOR . $parent->getName();
            $parent = $parent->getParent();
        }

        $filepath .= DIRECTORY_SEPARATOR . $page->getName();
        return self::DATA_DIRECTORY . $filepath . '_' . $language . '.json';
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
     * @param StorageInterface $storage
     * @return $this
     * @deprecated
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     * @deprecated
     */
    public function getStorage()
    {
        return $this->storage;
    }
}