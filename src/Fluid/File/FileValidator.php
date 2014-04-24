<?php
namespace Fluid\File;

class FileValidator
{
    const MAX_SIZE = 2097152;

    /**
     * @var FileEntity
     */
    private $file;

    /**
     * @param FileEntity $file
     */
    public function __construct(FileEntity $file)
    {
        $this->file = $file;
    }

    /**
     * @param array|null $uploadedFile
     * @return bool
     */
    public function validate(array $uploadedFile = null)
    {
        if (null !== $uploadedFile) {
            return $this->validateUploadedFile($uploadedFile);
        }
        return true;
    }

    /**
     * @param array|null $file
     * @return bool
     */
    public function validateUploadedFile(array $file = null)
    {
        if ($file['size'] <= self::MAX_SIZE && is_file($file['tmp_name']) && strpos($file['tmp_name'], sys_get_temp_dir()) === 0) {
            return true;
        }
        return false;
    }
}