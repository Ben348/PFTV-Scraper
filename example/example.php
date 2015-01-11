<?php
/**
 * Example 1
 * 
 * Example of how to use the script
 * 
 * @package     PFTV
 * @subpackage  example
 * @access      public
 * @version     1.0
 */

// For debugging
ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

// Include the file
require_once('../pftv.php');

// Make a new instance with our date format
$pftv = new PFTV('d M Y');

// Request a list of TV shows
$info = $pftv->get_tv_info('the_big_bang_theory');

var_dump($info);

