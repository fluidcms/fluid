<?php

namespace Fluid;

class Init {
    private static $allowed = array(
        '/fluidcms/stylesheets/init-1.0.css',
        '/fluidcms/images/preloader.gif',
        '/fluidcms/javascripts/vendor/jquery.js'
    );

    /**
     * Init Fluid from the web interface
     *
     * @return  void
     */
    public static function init() {

        if (!in_array($_SERVER['PHP_SELF'], self::$allowed)) {
            if ($_SERVER['PHP_SELF'] == '/fluidcms/init') {
                self::triggerInit();
                return;
            }

            View::setTemplatesDir(__DIR__ . "/Templates/");
            View::setLoader(null);
            echo View::create('init.twig');
            exit;
        }
    }

    /**
     * Trigger the init from ajax
     *
     * @return  string
     */
    public static function triggerInit() {
        Tasks\Init::execute();
        exit;
    }
}