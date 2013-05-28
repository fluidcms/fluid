<?php

namespace Fluid\Admin;

use Fluid, Exception;

/**
 * Route requests to admin interface.
 *
 * @package fluid
 */
class Router
{
    private static $request, $method, $input;

    /**
     * Route an admin request
     *
     * @param   string  $request
     * @return  mixed
     */
    public static function route($request)
    {
        Fluid\View::setTemplatesDir(__DIR__ . "/Templates/");
        Fluid\View::setLoader(null);

        self::$request = $request;

        if (isset($_SERVER['REQUEST_METHOD'])) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            self::$method = 'GET';
        }

        if (isset($_REQUEST) && is_array($_REQUEST) && count($_REQUEST)) {
            self::$input = $_REQUEST;
        } else {
            $input = file_get_contents("php://input");
            if (!empty($input)) {
                self::$input = json_decode($input, true);
            }
        }

        // Files
        if (!empty($request) && strpos($request, 'files/') === 0) {
            if (strpos($request, 'files/preview/') === 0) {
                $preview = true;
                $request = preg_replace('{^files/preview/}', 'files/', $request);
            }
            $request = urldecode($request);
            $file = Fluid\Fluid::getBranchStorage() . "files/" . substr($request, 6, 8) . '_' . substr($request, 15);
            if (file_exists($file)) {
                if (!isset($preview)) {
                    return new Fluid\StaticFile($file);
                } else {
                    $content = Fluid\Models\File::makePreview($file);
                    return new Fluid\StaticFile($content, 'jpg', true);
                }
            }
        }

        // File
        if (!empty($request) && strpos($request, "file/update/") === 0) {
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
                return Fluid\Models\File::delete(basename($request));
            }
        }

        // Site
        if (!empty($request) && $request === 'site') {
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                Fluid\Models\Site::update(file_get_contents("php://input"));
                return json_encode(true);
            }
        }

        // Other files
        switch ($request) {
            // Test
            case 'test':
                return Fluid\View::create('test.twig');

            // Structure
            case 'structure.json':
                if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'PUT') {
                    Fluid\Models\Structure::save(file_get_contents("php://input"));
                    return json_encode(true);
                }
                return json_encode(Fluid\Models\Structure::getAll());

            // Accept files
            case 'upload':
                if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $file = Fluid\Models\File::save();
                    return json_encode($file);
                }
                break;

            // Files
            case 'files.json':
                return json_encode(Fluid\Models\File::getFiles());
        }

        return (
            self::publicFiles() ||
                self::htmlPages() ||
                self::structure() ||
                self::page() ||
                self::languages() ||
                self::layouts() ||
                self::pageToken() ||
                Fluid\Fluid::NOT_FOUND
        );
    }

    /**
     * Output a page token.
     *
     * @return  bool
     */
    private static function pageToken()
    {
        if (self::$request == 'pagetoken.json') {
            echo json_encode(array('token' => Fluid\Models\PageToken::getToken()));
            return true;
        }
        return false;
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
                new Fluid\StaticFile($file);
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
            echo Fluid\View::create(
                'master.twig',
                array(
                    'site_url' => Fluid\Fluid::getConfig('url'),
                    'token' => Fluid\Models\PageToken::getToken(),
                    'branch' => 'develop'
                )
            );
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
            Fluid\Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(Fluid\Models\Language::getLanguages());
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
            Fluid\Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(Fluid\Models\Layout::getLayouts());
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
    private static function structure()
    {
        if (!empty(self::$request) && preg_match('{^([a-z0-9]*)/(structure)(/.*)?$}', self::$request, $match)) {
            $branch = $match[1];
            Fluid\Fluid::switchBranch($branch);
            switch (self::$method) {
                case 'GET':
                    echo json_encode(Fluid\Models\Structure::getAll());
                    return true;
                case 'POST':
                    try {
                        echo json_encode(Fluid\Models\Structure::createPage(self::$input));
                    } catch (Exception $e) {
                        header('X-Error-Message: ' . $e->getMessage(), true, 500);
                        exit;
                    }
                    return true;
                case 'PUT':
                    // Sort
                    if (isset($match[3]) && strpos($match[3], '/sort') == 0) {
                        try {
                            $id = trim(urldecode(preg_replace('{/sort/}', '', $match[3])), '/');
                            echo json_encode(Fluid\Models\Structure::sortPage($id, self::$input['page'], self::$input['index']));
                        } catch(Exception $e) {
                            header('X-Error-Message: ' . $e->getMessage(), true, 500);
                            exit;
                        }
                    }
                    return true;
                case 'DELETE':
                    try {
                        echo json_encode(Fluid\Models\Structure::deletePage(trim(urldecode($match[3]), '/')));
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
                        $data = Fluid\Models\Page::mergeTemplateData(isset($_POST['content']) ? $_POST['content'] : '');
                        echo json_encode(array(
                            'language' => Fluid\Fluid::getLanguage(),
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
                        echo json_encode(Fluid\Models\Page::update(trim(urldecode($match[3]), '/'), self::$input));
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
}
