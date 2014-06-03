<?php
/**
 * Entry file for WebExt framework.
 *
 * It defines the WEB constant for direct accesschecks,
 * defines constants to get rid of some global var use,
 * registers an autoclassloader and offers a function
 * to check for defined SMF and WEB constants.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Global
 * @license BSD
 * @copyright 2014 by author
 */

// Check for direct file access
if (!defined('SMF'))
    die('No direct access...');

// Define that the WebExt has been loaded
if (!defined('WEB'))
    define('WEB', 1);

    // Define some constants for better code handling without globals
define('SOURCEDIR', $sourcedir);
define('BOARDDIR', $boarddir);
define('BOARDURL', $boardurl);
define('CACHEDIR', $cachedir);
define('WEBDIR', $boarddir . '/Web');

// Register classloader
require_once (WEBDIR . '/Framework/Tools/autoload/SplClassLoader.php');

$loader = new SplClassLoader('Web', BOARDDIR);
$loader->register();
?>
