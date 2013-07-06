<?php

namespace Fluid;

use Exception;

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
            self::htmlPages() ||
            self::map() ||
            self::page() ||
            self::languages() ||
            self::layouts() ||
            self::pageToken() ||
            self::version() ||
            self::file()
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
     * Output a page token.
     *
     * @return  bool
     */
    private static function pageToken()
    {
        if (self::$request == 'pagetoken.json') {
            echo json_encode(array('token' => Models\PageToken::getToken()));
            return true;
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

            echo View::create(
                'master.twig',
                array(
                    'websocket_url' => preg_replace('!^https?://([^/]*)!i', "ws://$1:" . Fluid::getConfig('ports')['websockets'], $url),
                    'user_id' => uniqid(),
                    'site_url' => $url,
                    'branch' => 'develop'
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

    /**
     * Route languages requests.
     *
     * @return  bool
     */
    private static function languages()
    {
        if (!empty(self::$request) && preg_match('{^([a-z0-9]*)/(languages)(/.*)?$}', self::$request, $match)) {
            $branch = $match[1];
            Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(Models\Language::getLanguages());
                    return true;
            }
        }
        return false;
    }

    /**
     * Route layouts requests.
     *
     * @return  bool
     */
    private static function layouts()
    {
        if (!empty(self::$request) && preg_match('{^([a-z0-9]*)/(layouts)(/.*)?$}', self::$request, $match)) {
            $branch = $match[1];
            Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(Models\Layout::getLayouts());
                    return true;
            }
        }
        return false;
    }

    /**
     * Route file requests.
     *
     * @return  bool
     */
    private static function file()
    {
        // Files and files preview
        if (preg_match('{^([a-z0-9]*)/files/(.*)}', self::$request, $match)) {
            $file = $match[2];
            $branch = $match[1];
            Fluid::setBranch($branch, true);
            if (strpos($file, 'preview/') === 0) {
                $preview = true;
                $file = preg_replace('{^preview/}', '', $file);
            }
            $file = urldecode($file);
            $file = Fluid::getBranchStorage() . "files/" . substr($file, 0, 8) . '_' . substr($file, 9);
            if (file_exists($file)) {
                if (!isset($preview)) {
                    new StaticFile($file);
                    return true;
                } else {
                    $content = File\File::makePreview($file);
                    new StaticFile($content, 'png', true);
                    return true;
                }
            }
        }

        // File model
        if (preg_match('{^([a-z0-9]*)/file}', self::$request, $match)) {
            $branch = $match[1];
            Fluid::setBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(File\File::getFiles());
                    return true;
                case 'POST':
                    echo json_encode(File\File::save());
                    return true;
                case 'DELETE':
                    // File
                    echo json_encode(File\File::delete(basename(self::$request)));
                    return true;
            }
        }

        return false;
    }

    /**
     * Route structure requests.
     *
     * @return  bool
     */
    private static function map()
    {
        if (!empty(self::$request) && preg_match('{^([a-z0-9]*)/(map)(/.*)?$}', self::$request, $match)) {
            $branch = $match[1];
            Fluid::setBranch($branch, true);
            switch (self::$method) {
                case 'GET':
                    $map = new Map\Map;
                    echo json_encode($map->getPages());
                    return true;

                case 'POST':
                    try {
                        echo json_encode(Models\Structure::createPage(self::$input));
                    } catch (Exception $e) {
                        header('X-Error-Message: ' . $e->getMessage(), true, 500);
                        exit;
                    }
                    return true;
                case 'PUT':
                    // Sort
                    if (isset($match[3]) && strpos($match[3], '/sort') === 0) {
                        try {
                            $id = trim(urldecode(preg_replace('{/sort/}', '', $match[3])), '/');
                            echo json_encode((new Map\Map)->sortPage($id, self::$input['page'], self::$input['index']));
                        } catch(Exception $e) {
                            header('X-Error-Message: ' . $e->getMessage(), true, 500);
                            exit;
                        }
                    }
                    // Edit
                    else {
                        try {
                            echo json_encode(Models\Structure::editPage(self::$input));
                        } catch (Exception $e) {
                            header('X-Error-Message: ' . $e->getMessage(), true, 500);
                            exit;
                        }
                        return true;
                    }
                    return true;
                case 'DELETE':
                    try {
                        echo json_encode(Models\Structure::deletePage(trim(urldecode($match[3]), '/')));
                    } catch(Exception $e) {
                        header('X-Error-Message: ' . $e->getMessage(), true, 500);
                        exit;
                    }
                    return true;
            }
        }

        return false;
    }

    /**
     * Route page requests.
     *
     * @return  bool
     */
    private static function page()
    {
        if (!empty(self::$request) && preg_match('{^([a-z0-9]*)/(page)(/.*)?$}', self::$request, $match)) {
            $branch = $match[1];
            Fluid\Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'POST':
                    if (empty($match[3])) {
                        $data = Models\Page::mergeTemplateData(isset($_POST['content']) ? $_POST['content'] : '');
                        echo json_encode(array(
                            'language' => Fluid::getLanguage(),
                            'page' => $data['page']->page,
                            'data' => $data['page']->data,
                            'variables' => $data['page']->variables,
                            'site' => array(
                                'data' => $data['site']->data,
                                'variables' => $data['site']->variables
                            )
                        ));

                    }
                    return true;
                case 'PUT':
                    try {
                        echo json_encode(Models\Page::update(trim(urldecode($match[3]), '/'), self::$input));
                    } catch(Exception $e) {
                        header('X-Error-Message: ' . $e->getMessage(), true, 500);
                        exit;
                    }
                    return true;
                case 'DELETE':
                    return true;
            }
        }
        return false;
    }

    /**
     * Route version requests.
     *
     * @return  bool
     */
    private static function version()
    {
        // Update master
        if (self::$request == 'update') {
            Tasks\FetchPull::execute('master');
        }

        // Other
        if (preg_match('{^([a-z0-9]*)/(commit\+push|pull)$}', self::$request, $match)) {
            $action = $match[2];
            $branch = $match[1];
            Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'POST':
                    if ($action === 'commit+push') {
                        Tasks\CommitPush::execute($branch, self::$input["msg"]);
                        // Fluid\Task::run("CommitPush", array($branch, self::$input["msg"])); TODO not working?
                        return true;
                    }
                    break;
                case 'GET':
                    if ($action === 'pull') {
                        Git::pull($branch);
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
