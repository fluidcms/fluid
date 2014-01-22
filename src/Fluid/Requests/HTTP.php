<?php

namespace Fluid\Requests;

use Fluid\Config;
use Fluid\Event;
use Fluid\Fluid;
use Fluid\StaticFile;
use Fluid\Session\Session;
use Fluid\View;
use Fluid\Daemon\Daemon;

/**
 * Route manager requests
 *
 * @package fluid
 */
class HTTP
{
    private static $request;
    private static $method;
    private static $input;

    /**
     * Route an admin request
     *
     * @param string $request
     * @param string|null $method
     * @param array|null $input
     * @return mixed
     */
    public static function route($request, $method = null, array $input = null)
    {
        if (stripos($request, '/fluidcms/') === 0) {
            $request = substr($request, 10);
        }

        self::$request = ltrim($request, '/');

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

        if ($response = self::serverStatus()) {
            return $response;
        } elseif ($response = self::tmpImages()) {
            return $response;
        } elseif ($response = self::files()) {
            return $response;
        } elseif ($response = self::upload()) {
            return $response;
        } elseif ($response = self::publicFiles()) {
            return $response;
        } elseif ($response = self::javascriptFiles()) {
            return $response;
        } elseif ($response = self::changePage()) {
            return $response;
        } elseif ($response = self::htmlPages()) {
            return $response;
        }
        return null;
    }

    /**
     * Returns the server status and tries to start it if it is shut down
     *
     * @return bool
     */
    private static function serverStatus()
    {
        if (!empty(self::$request) && self::$method === 'POST' && strpos(self::$request, 'server') === 0 && isset(self::$input['session'])) {
            if (Session::validate(self::$input['session'])) {
                if (Daemon::isRunning()) {
                    // Daemon is already running
                    return json_encode(true);
                } else if (Daemon::runBackground()) {
                    // Start Daemon
                    return json_encode(true);
                } else {
                    // Could not start Daemon
                    return json_encode(false);
                }
            }
        }

        return false;
    }

    /**
     * Create tmp images with provided dimensions, required for fluid (not the cms) layout
     *
     * @return bool
     */
    private static function tmpImages()
    {
        if (!empty(self::$request) && strpos(self::$request, 'images/imgtmp-') === 0) {
            if (preg_match('!images/imgtmp-(\d+|NaN)px-(\d+|NaN)px\.gif!', self::$request, $matches)) {
                if ($matches[1] === 'NaN') {
                    $matches[1] = 1;
                }
                if ($matches[2] === 'NaN') {
                    $matches[2] = 1;
                }
                $im = imagecreatetruecolor($matches[1], $matches[2]);
                imagefilledrectangle($im, 0, 0, $matches[1], $matches[2], 0xe5e5e5);
                header('Content-Type: image/gif');
                imagegif($im);
                imagedestroy($im);
                return true;
            }
        }
        return false;
    }

    /**
     * Route cms files
     *
     * @return bool
     */
    private static function files()
    {
        if (!empty(self::$request) && strpos(self::$request, 'images/') === 0) {
            $found = null;
            $dir = Config::get('storage');
            $file = preg_replace('!images/!', 'files/', urldecode(self::$request));

            if (is_dir($dir)) {
                foreach (scandir($dir) as $branch) {
                    if ($branch !== '.' && $branch !== '..') {
                        if (file_exists("{$dir}/{$branch}/{$file}")) {
                            new StaticFile("{$dir}/{$branch}/{$file}");
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Upload file to CMS
     *
     * @return bool
     */
    private static function upload()
    {
        if (
            !empty(self::$request) &&
            strpos(self::$request, 'file') === 0 &&
            self::$method === 'POST' &&
            count($_FILES) &&
            isset(self::$input['topic']) &&
            isset(self::$input['id'])
        ) {
            $topic = json_decode(self::$input['topic'], true);
            if (isset($topic['branch']) && isset($topic['user_id']) && isset($topic['user_name']) && isset($topic['user_email'])) {
                foreach ($_FILES as $file) {
                    if (!$file['error']) {
                        break;
                    }
                }
                if (isset($file)) {
                    new WebSockets\WebSocket(
                        self::$request,
                        self::$method,
                        array(
                            'id' => self::$input['id'],
                            'file' => $file
                        ),
                        $topic['branch'],
                        array(
                            'id' => $topic['user_id'],
                            'name' => $topic['user_name'],
                            'email' => $topic['user_email']
                        )
                    );
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Route public files.
     *
     * @return bool
     */
    private static function publicFiles()
    {
        if (!empty(self::$request)) {
            $file = trim(self::$request, ' ./');
            $file = str_replace('..', '', $file);
            $file = realpath(__DIR__ . "/../../../public/{$file}");
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
     * @return bool
     */
    private static function javascriptFiles()
    {
        if (!empty(self::$request)) {
            $request = preg_replace('/javascripts/i', '', self::$request);
            $file = '/Javascripts/' . trim($request, ' ./');
            $file = str_replace('..', '', $file);
            $file = realpath(__DIR__ . "/..{$file}");
            if (file_exists($file)) {
                new StaticFile($file);
                return true;
            }
        }
        return false;
    }

    /**
     * Change language script.
     *
     * @return bool
     */
    private static function changePage()
    {
        if (!empty(self::$request) && self::$request === 'changepage.json') {
            $url = isset($_GET['url']) ? filter_var($_GET['url'], FILTER_SANITIZE_STRING) : '';
            $language = isset($_GET['language']) ? filter_var($_GET['language'], FILTER_SANITIZE_STRING) : 'en-US';

            foreach (Event::trigger('changePage', array($url, $language)) as $retval) {
                $url = $retval;
            }

            return json_encode($url);
        }
        return false;
    }

    /**
     * Route html pages.
     *
     * @return bool|string
     */
    private static function htmlPages()
    {
        if (self::$request == '' || self::$request == 'files') {
            if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
                $url = 'https://';
            } else {
                $url = 'http://';
            }

            $url .= "{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url = preg_replace("!/fluidcms/(.*)$!i", "/", $url);

            $GLOBALS['websocket_url'] = preg_replace('!^https?://([^/]*)!i', "ws://$1:80", $url);
            $GLOBALS['user_id'] = uniqid();
            $GLOBALS['site_url'] = $url;
            $GLOBALS['branch'] = 'develop';
            $GLOBALS['session'] = Session::create();
            $GLOBALS['language'] = require __DIR__ . "/../Locale/en-US.php";

            return require __DIR__ . "/../Templates/app.php";
        }
        return false;
    }
}
