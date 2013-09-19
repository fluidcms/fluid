<?php

namespace Fluid\Requests;

use Fluid\Fluid,
    Fluid\Events as FluidEvents,
    Fluid\WebSocket\Events as ServerEvents,
    Fluid\Language\Language,
    Fluid\Layout\Layout,
    Fluid\Map\Map,
    Fluid\Page\Page,
    Fluid\File\File,
    Fluid\Token\Token,
    Fluid\Component\Component,
    Fluid\History\History;

class WebSocket
{
    private $request;
    private $method;
    private $input = array();
    private $branch;
    private $user = array();

    /**
     * Route a request
     *
     * @param   string  $request
     * @param   string  $method
     * @param   array   $input
     * @param   string  $branch
     * @param   array   $user
     */
    public function __construct($request, $method, $input = array(), $branch = null, $user = null)
    {
        $this->user = $user;
        $this->branch = $branch;
        $this->request = $request;
        $this->method = $method;
        $this->input = $input;

        Fluid::setBranch($branch, true);

        $this->map() ||
        $this->page() ||
        $this->language() ||
        $this->layout() ||
        $this->component() ||
        $this->token() ||
        $this->version() ||
        $this->file() ||
        $this->history();
    }

    /**
     * Output a page token.
     *
     * @return  bool
     */
    private function token()
    {
        if ($this->request === 'token' && $this->method === 'GET') {
            $token = Token::get();
            echo json_encode(array('token' => $token));
            return true;
        }
        return false;
    }

    /**
     * Route languages requests.
     *
     * @return  bool
     */
    private function language()
    {
        if (!empty($this->request) && preg_match('{^(language)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    echo json_encode(Language::getLanguages());
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
    private function layout()
    {
        if (!empty($this->request) && preg_match('{^(layout)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    // Get all
                    if (empty($match[2])) {
                        echo json_encode(Layout::getLayouts());
                        return true;
                    }
                    // Get specific layout
                    else {
                        $layout = trim($match[2], '/ ');
                        $layout = Layout::get($layout);
                        echo json_encode($layout->getVariables());
                        return true;
                    }
            }
        }
        return false;
    }

    /**
     * Route layouts requests.
     *
     * @return  bool
     */
    private function component()
    {
        if (!empty($this->request) && preg_match('{^(component)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    // Get all
                    if (empty($match[2])) {
                        $components = Component::getComponents();
                        echo json_encode($components);
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * Route file requests
     *
     * @return  bool
     */
    private function file()
    {
        // Collection methods
        if (!empty($this->request) && preg_match('{^(files)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    echo json_encode(File::getFiles());
                    return true;
            }
        }

        // Model methods
        if (!empty($this->request) && preg_match('{^(file)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    // Get file preview
                    if (isset($match[2]) && strpos($match[2], '/preview/') === 0) {
                        $id = substr($match[2], 9);
                        echo json_encode(File::get($id)->getPreview());
                    }
                    return true;
                case 'POST':
                    if (File::upload($this->input["id"], $this->input["file"])) {
                        echo json_encode(File::get($this->input["id"])->getInfo());
                    }
                    return true;
                case 'DELETE':
                    // File
//                    echo json_encode(File\File::delete(basename($this->request)));
                    return true;
            }
        }

        return false;
    }

    /**
     * Route map requests.
     *
     * @return  bool
     */
    private function map()
    {
        if (!empty($this->request) && preg_match('{^(map)(/.*)?$}', $this->request, $match)) {
            ServerEvents::register($this->user['id'], 'map');

            switch ($this->method) {
                case 'GET':
                    $map = new Map;
                    echo json_encode($map->getPages());
                    return true;

                case 'POST':
                    $map = new Map;
                    $map->createPage($this->input);
                    History::add(
                        'map_add',
                        $this->user['name'],
                        $this->user['email']
                    );
                    FluidEvents::trigger('mapChange', array('branch' => $this->branch));
                    echo json_encode($map->getPages());
                    return true;

                case 'PUT':
                    // Sort
                    if (isset($match[2]) && strpos($match[2], '/sort') === 0) {
                        $id = trim(urldecode(preg_replace('{/sort/}', '', $match[2])), '/');
                        $map = new Map;
                        $retval = $map->sortPage($id, $this->input['page'], $this->input['index']);
                        History::add(
                            'map_sort',
                            $this->user['name'],
                            $this->user['email']
                        );
                        echo json_encode($map->getPages());
                    }
                    // Edit
                    else {
                        $map = new Map;
                        $map->editPage($this->input);
                        History::add(
                            'map_edit',
                            $this->user['name'],
                            $this->user['email']
                        );
                        echo json_encode($map->getPages());
                    }
                    FluidEvents::trigger('mapChange', array('branch' => $this->branch));
                    return true;

                case 'DELETE':
                    $map = new Map;
                    $map->deletePage(trim(urldecode($match[2]), '/'));
                    History::add(
                        'map_delete',
                        $this->user['name'],
                        $this->user['email']
                    );
                    echo json_encode($map->getPages());
                    FluidEvents::trigger('mapChange', array('branch' => $this->branch));
                    return true;
            }
        }

        return false;
    }

    /**
     * Route history requests.
     *
     * @return  bool
     */
    private function history()
    {
        if (!empty($this->request) && preg_match('{^(history)(/.*)?$}', $this->request, $match)) {
            switch ($this->method) {
                case 'GET':
                    $history = new History;
                    echo json_encode($history->getAll());
                    return true;

                case 'PUT':
                    if (isset($match[2])) {
                        $id = trim($match[2], ' /.');
                        $history = History::rollBack($id);
                        FluidEvents::trigger('historyChange', array('branch' => $this->branch));
                        echo json_encode($history->getAll());
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
    private function page()
    {
        if (!empty($this->request) && preg_match('{^(page)(/[a-z]{2,2}\-[A-Z]{2,2})(/.*)?$}', $this->request, $match)) {
            $page = null;
            if (isset($match[3])) {
                $page = trim($match[3], '/ ');
                if ($page === 'global') {
                    $page = null;
                }
            }
            $language = null;
            if (isset($match[2])) {
                $language = trim($match[2], '/ ');
            }

            switch ($this->method) {
                case 'GET':
                    $output = array();
                    if ($page === null) {
                        $page = Page::get(null, $language);
                        $output = array(
                            'data' => $page->getRawData(),
                            'layoutDefinition' => Layout::get('global')->getVariables()
                        );
                    } else {
                        $map = new Map;
                        if ($mapPage = $map->findPage($page)) {
                            $page = Page::get($mapPage, $language);

                            $output = array_merge(
                                $mapPage,
                                array(
                                    'data' => $page->getRawData(),
                                    'layoutDefinition' => Layout::get($page->getLayout())->getVariables()
                                )
                            );
                        }
                    }

                    echo json_encode($output);
                    return true;

                case 'PUT':
                    $output = array();

                    if ($page === null) {
                        $page = Page::get(null, $language);
                    } else {
                        $map = new Map;
                        if ($mapPage = $map->findPage($page)) {
                            $page = Page::get($mapPage, $language);
                        }
                    }

                    // Update page
                    if ($page instanceof Page) {
                        $page->update($this->input);

                        History::add(
                            'page_edit',
                            $this->user['name'],
                            $this->user['email']
                        );
                    }

                    // Get page new data
                    if (isset($mapPage) && $mapPage) {
                        $output = array_merge(
                            $mapPage,
                            array(
                                'data' => $page->getRawData(),
                                'layoutDefinition' => Layout::get($page->getLayout())->getVariables()
                            )
                        );
                    } else {
                        $output = array(
                            'data' => $page->getRawData(),
                            'layoutDefinition' => Layout::get('global')->getVariables()
                        );
                    }

                    echo json_encode($output);
                    return true;
            }
        }

        // Get specific variable
        if (!empty($this->request) && preg_match('{^(page_variable)(/[a-z]{2,2}\-[A-Z]{2,2})(/.*)?(/[^/]*)/([^/]*)$}', $this->request, $match)) {
            $language = null;
            if (isset($match[2])) {
                $language = trim($match[2], '/ ');
            }

            $item = null;
            if (isset($match[5])) {
                $item = trim($match[5], '/ ');
            }

            $group = null;
            if (isset($match[4])) {
                $group = trim($match[4], '/ ');
            }

            $page = null;
            if (isset($match[3])) {
                $page = trim($match[3], '/ ');

                if (empty($page) && null !== $group) {
                    $page = $group;
                    $group = null;
                } else if ($page === 'global') {
                    $page = null;
                }
            }

            $retval = array();
            if ($page === null) {
                $page = Page::get(null, $language);
                $retval = $page->getVariable($item, $group);
            } else {
                $map = new Map;
                if ($mapPage = $map->findPage($page)) {
                    $page = Page::get($mapPage, $language);
                    $retval = $page->getVariable($item, $group);
                }
            }
            echo json_encode($retval);
        }

        return false;
    }

    /**
     * Route version requests.
     *
     * @return  bool
     */
    private function version()
    {
        // Update master
        if ($this->request == 'update') {
            Tasks\FetchPull::execute('master');
        }

        // Other
        if (preg_match('{^([a-z0-9]*)/(commit\+push|pull)$}', $this->request, $match)) {
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