<?php

namespace Fluid\Tasks;

use Fluid\Fluid;

class Init {
    /*
     * Create git repository and default branches
     *
     * @return  void
     */
    public static function execute() {
        Branch::execute('master');
        Database::execute();

        $dir = Fluid::getConfig('storage') . ".data/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
}
