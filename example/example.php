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

// Include the file
require_once('../pftv.php');

// Make a new instance with our date format
$pftv = new PFTV('d M Y');

// Request a list of TV shows