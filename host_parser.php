<?php
/**
 * Host parser class
 * 
 * Base class for video hosts. Each host will need it's own class which will
 * extract the direct video link and return it.
 * 
 * @package     PFTV
 * @subpackage  classes
 * @access      public
 * @version     1.0
 * @author      Ben Thomson <bathomson93@gmail.com>
 */
abstract class host_parser
{
    /**
     * Initialisation happens here
     * @return  void
     */
    function __construct(){}

    /**
     * Clear up
     * @return  void
     */
    function __destruct(){}

    /**
     * Get the direct video link
     * @param   string      $embedded_url   Embedded url for the host
     * @return  string|Exception Direct video link if successful or error if not
     */
    public static function get_link($embedded_url){}
}