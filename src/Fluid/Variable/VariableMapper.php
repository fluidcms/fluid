<?php
namespace Fluid\Variable;

use Fluid\Language\LanguageEntity;
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
     */
    private $storage;

    /**
     * @param LanguageEntity $language
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage, LanguageEntity $language)
    {
        $this->setLanguage($language);
        $this->setStorage($storage);
    }

    /**
     * @param VariableCollection $collection
     */
    public function persist(VariableCollection $collection)
    {
        $file = $this->getFile($collection->getPage()->getName(), $this->getLanguage()->getLanguage());
        $this->getStorage()->saveBranchData($file, $collection->toArray());
    }

    /**
     * @param VariableCollection $collection
     */
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        $file = $this->getFile($collection->getPage()->getName(), $this->getLanguage()->getLanguage());
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
     * @param string $page
     * @param string $language
     * @return string
     */
    private function getFile($page, $language)
    {
        return self::DATA_DIRECTORY . DIRECTORY_SEPARATOR . $page . '_' . $language . '.json';
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
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}