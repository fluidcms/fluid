<?php

namespace Fluid;

use Twig_Loader_Filesystem, Twig_Environment;

/**
 * Loader interface
 *
 * @package fluid
 */
interface TwigLoaderInterface {
	public static function loader();
}