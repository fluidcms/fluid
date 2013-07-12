<?php

namespace Fluid;

/**
 * Route manager requests
 *
 * @package fluid
 */
class ManagerRouter
{
    private static $request, $method, $input;

    /**
     * Route an admin request
     *
     * @param   string  $request
     * @param   string  $method
     * @param   array   $input
     * @return  mixed
     */
    public static function route($request, $method = null, $input = null)
    {
        self::$request = $request;

        // Get request method
        if (null !== $method) {
            self::$method = $method;
        } else if (isset($_SERVER['REQUEST_METHOD'])) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            self::$method = 'GET';
        }

        // Get request input
        if (null !== $input) {
            self::$input = $input;
        } else if (isset($_REQUEST) && is_array($_REQUEST) && count($_REQUEST)) {
            self::$input = $_REQUEST;
        } else {
            $fluidInput = Fluid::getRequestPayload();
            $input = file_get_contents("php://input");
            if (!empty($input)) {
                self::$input = json_decode($input, true);
            } else if (!empty($fluidInput)) {
                self::$input = json_decode($fluidInput, true);
            }
        }

        return (
            self::publicFiles() ||
            self::javascriptFiles() ||
            self::htmlPages()
        );
    }

    /**
     * Route public files.
     *
     * @return  bool
     */
    private static function publicFiles()
    {
        if (!empty(self::$request)) {
            $file = __DIR__ . '/Public/' . trim(self::$request, ' ./');
            $file = str_replace('..', '', $file);
            if (file_exists($file)) {
                new StaticFile($file);
                return true;
            }
        }
        return false;
    }

    /**
     * Route javascript files.
     *
     * @return  bool
     */
    private static function javascriptFiles()
    {
        if (!empty(self::$request)) {
            $request = preg_replace('/javascripts/i', '', self::$request);
            $file = __DIR__ . '/Javascripts/' . trim($request, ' ./');
            $file = str_replace('..', '', $file);
            if (file_exists($file)) {
                new StaticFile($file);
                return true;
            }
        }
        return false;
    }

    /**
     * Route html pages.
     *
     * @return  bool
     */
    private static function htmlPages()
    {
        if (self::$request == '' || self::$request == 'files') {
            View::setTemplatesDir(__DIR__ . "/Templates/");
            View::setLoader(null);

            if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
                $url = 'https://';
            } else {
                $url = 'http://';
            }

            $url .= "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url = preg_replace("!/fluidcms/(.*)$!i", "/", $url);

            $ports = Fluid::getConfig('ports');

            echo View::create(
                'master.twig',
                array(
                    'websocket_url' => preg_replace('!^https?://([^/]*)!i', "ws://$1:" . $ports['websockets'], $url),
                    'user_id' => uniqid(),
                    'site_url' => $url,
                    'branch' => 'develop',
                    'language' => require __DIR__ . "/Locale/en-US.php"
                )
            );
            return true;
        }

        else if (self::$request == 'test') {
            View::setTemplatesDir(__DIR__ . "/Templates/");
            View::setLoader(null);

            echo View::create('test.twig');
            return true;
        }

        return false;
    }
}
