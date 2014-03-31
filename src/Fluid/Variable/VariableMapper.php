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
    public function mapCollection(VariableCollection $collection)
    {
        $variables = $collection->getPage()->getTemplate()->getVariables();
        $file = self::DATA_DIRECTORY . DIRECTORY_SEPARATOR . $collection->getPage()->getName() . '_' . $this->getLanguage()->getLanguage() . '.json';
        $data = $this->getStorage()->loadBranchData($file);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $variable = $variables->find($key);
                if ($variable) {
                    $variable->setValue($value);
                }
            }
        }
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