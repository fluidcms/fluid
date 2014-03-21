<?php

namespace Fluid\Tasks;

use Fluid\Config;

class Init {
    /*
     * Create git repository and default branches
     *
     * @return  void
     */
    public static function execute() {
        Branch::execute('master');
        Database::execute();

        $dir = Config::get('storage') . "/.data/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
}
