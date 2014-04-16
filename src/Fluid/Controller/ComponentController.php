<?php
namespace Fluid\Controller;

use Fluid\Component\ComponentCollection;
use Fluid\Component\ComponentMapper;
use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Helper\SessionHelperInterface;
use Fluid\Response;
use Fluid\StaticFile;

class ComponentController extends Controller implements SessionHelperInterface
{
    use SessionHelper;

    /**
     *
     */
    public function getAll()
    {
        if ($this->validSession()) {
            $components = new ComponentCollection($this->getStorage(), $this->getXmlMappingLoader());

            $this->getResponse()->json($components->toArray());
            return;
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }

    /**
     * @param string $icon
     */
    public function icon($icon)
    {
        // Sanitize path
        $icon = str_replace('../', '', $icon);
        $icon = preg_replace('!/{2,}!', '/', $icon);

        $dir = realpath($this->getConfig()->getMapping() . DIRECTORY_SEPARATOR . ComponentMapper::MAPPING_DIRECTORY) . DIRECTORY_SEPARATOR;
        $file = realpath($dir . $icon);

        if (!empty($file) && strpos($file, $dir) === 0 && is_file($file) && exif_imagetype($file) !== false) {
            new StaticFile($file);
            return;
        }

        $this->getResponse()->setCode(Response::RESPONSE_CODE_NOT_FOUND);
    }
}