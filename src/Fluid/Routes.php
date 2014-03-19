<?php

use Fluid\Controllers;

return function() {
    return [
        '/admin/' => function() { return (new Controllers\AdminController())->index(); },
    ];
};