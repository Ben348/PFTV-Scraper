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

// TV show id
$show_id = 'the_big_bang_theory';
$category_id = 'season_1.html';

// Request information for the tv show
$info = json_decode(json_encode($pftv->get_tv_info($show_id)));
var_dump($info);

