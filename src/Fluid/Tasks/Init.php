<?php

namespace Fluid\Tasks;

class Init {
    /*
     * Create git repository and default branches
     *
     * @return  void
     */
    public static function execute() {
        Bare::execute();
        Branch::execute('master');
        Database::execute();
    }
}
