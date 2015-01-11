<?php
/**
 * Scraping PFTV class
 * 
 * Contains all the functions needed to gather information from
 * the Project Free TV website.
 * 
 * @package     PFTV
 * @subpackage  classes
 * @access      public
 * @version     1.0
 * @author      Ben Thomson <bathomson93@gmail.com>
 */
class pftv
{
    /**
     * Base url for the PFTV website
     * @access  private
     * @var     string
     */
    private $base_url = 'http://projectfreetv.club/';

    /**
     * TV show url path
     * @access  private
     * @var     string
     */
    private $tv_url = 'internet';

    /**
     * Movies url path
     * @access  private
     * @var     string
     */
    private $movie_url = 'movies';

    /**
     * Date format for date objects
     * @access  private
     * @var     string
     */
    private $date_format = 'd/m/Y';

    /**
     * Setup all the class with all the PHP settings and includes needed to function
     * @param   string      $date_format    Date format to return all dates in
     * @param   boolean     $custom_errors  Use the custom error handler (Handles warnings too)
     * @return  void
     */
    function __construct($date_format = 'd/m/Y', $custom_errors = true)
    {
        // Disable standard libxml errors (Lots of errors when converting the HTML to XML)
        libxml_use_internal_errors(true);

        // Include the host parser class
        require_once('host_parser.php');

        // Setup custom error handler
        set_error_handler(
            create_function(
                '$severity, $message, $file, $line',
                'throw new ErrorException($message, $severity, $severity, $file, $line);'
            )
        );
    }

    /**
     * Clear up
     * @return  void
     */
    function __destruct()
    {
        // Clear the libxml error buffer
        libxml_clear_errors();

        // Restore to previous error handling function
        restore_error_handler();
    }

    /**
     * Convert a string to a date object
     * @param   string      $str     String to convert
     * @return  string|null     Formatted date string or null if failed
     */
    private function format_date($str)
    {
        try
        {
            // Try to create a new date
            $date = new DateTime($str);

            // Return formatted date
            return $date->format($this->date_format);
        }
        catch(Exception $e)
        {
            // Call custom error handler function
            $this->handle_error($e);

            // Return null
            return null;
        }
    }

    /**
     * Handle error when they are caught in the try/catch block
     * can do custom stuff here.
     * @param   ErrorException  $exception  ErrorException object
     * @return  string      response
     */
    private function handle_error($exception)
    {
        try
        {
            // Output xdebug message
            echo "C:<br>";
            echo "<font size='1'><table class='xdebug-error xe-fatal-error' dir='ltr' border='1' cellspacing='0' cellpadding='1'>".$exception->xdebug_message."</table></font>";
        }
        catch(Exception $e)
        {
            // Return original error object
            return $exception;
        }
    }
}