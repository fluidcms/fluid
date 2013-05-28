<?php

namespace Fluid\Tasks;

class Init {
    /*
     * Create git repository and default branches
     *
     */
    public function __construct() {
        new Bare;
        new Branch('master');
        new Database;
    }
}
