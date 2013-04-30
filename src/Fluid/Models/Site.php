<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Database\Storage;

/**
 * Site model
 *
 * @package fluid
 */
class Site extends Storage
{
    public $data;
    public $variables;

    /**
     * Init
     *
     * @return  void
     */
    public function __construct()
    {
        // Load page data
        try {
            $this->data = self::load('site/site_' . Fluid::getLanguage() . '.json');
        } catch (Exception $e) {
            null;
        }
    }

    /**
     * Update the site
     *
     * @param   string  $page
     * @param   string  $reqyest
     * @return  void
     */
    public static function update($request)
    {
        $request = json_decode($request, true);
        $file = 'site/site_' . $request['language'] . '.json';
        self::save(json_encode($request['data'], JSON_PRETTY_PRINT), $file);
    }
}