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
     * Get TV show information including:
     * - Title
     * - Plot
     * - Image url
     * - List trailers
     * - Next episode (name, code, air date)
     * - List of categories / seasons
     * @param   string      $show_id    TV show id
     * @access  public
     * @return  array|ErrorException    Array of data or error if an error occurred
     * @todo    Impliment a way to handle special characters
     */
    public function get_tv_info($show_id)
    {
        try
        {
            // Build the website URL
            $url = $this->base_url.'/'.$this->tv_url.'/'.$show_id.'/';

            // Create a new DOMDocument
            $dom = new DOMDocument('1.0', 'iso-8859-1');

            // Load the website source to the document
            $dom->loadHTMLFile($url);

            // Create a new DOMXpath object
            $xpath = new DOMXpath($dom);

            // Assign default values
            $title = $plot = $image_url = $next_episode = null;
            $trailers = $categories = array();

            // Array of all our XPath expressions
            $expressions = (object) array(
                'title'         => 'string(//table[2]/tr/td[2]/table[1]/tr/td/h1//text())',
                'plot'          => '//*[@id="plot"]//text()',
                'image_url'     => 'string(//*[@id="vposter"]/@src)',
                'trailers'      => '//*[@id="trailers"]/a[@class="mnlcategorylist"]/@href',
                'categories'    => '//td[@class="mnlcategorylist"]',
                'next_episode'  => '//span[contains(@style,"color:#AE0000; font-size: 11px;")]'
            );

            // 1. Get the title
            $title = ($tmp = $xpath->evaluate($expressions->title)) ? $tmp : null;

            // 2. Get the plot. Concat all the text nodes
            foreach ($xpath->query($expressions->plot) as $node):
                $plot .= $node->nodeValue;
            endforeach;

            // Trim the white space
            $plot = $plot ? trim($plot) : null;

            // 3. Get the image thumbnail
            $image_url = ($tmp = $xpath->evaluate($expressions->image_url)) ? $tmp : null;

            // 4. Get a list of trailers
            foreach ($xpath->query($expressions->trailers) as $node):
                $trailers[] = $node->nodeValue;
            endforeach;

            // 5. Get a list of categories / seasons
            foreach ($xpath->query($expressions->categories) as $node)
            {
                // 5.1 Category name
                $category_name = ($tmp = $xpath->evaluate('string(.//a/b/text())', $node)) ? $tmp : null;

                // 5.2 Category id
                $category_id = ($tmp = $xpath->evaluate('string(.//a/@href)', $node)) ? $tmp : null;

                // 5.3 Category data (Links & episode counters)
                if(($category_data = $xpath->evaluate('string(text())', $node))):
                    preg_match('/(?P<episodes>\d+).*\s(?P<links>\d+)/i', $category_data, $matches);
                endif;

                // Add to category array
                $categories[] = array(
                    'id'        => $category_id,
                    'name'      => $category_name,
                    'episodes'  => isset($matches['episodes']) ? $matches['episodes'] : 0,
                    'links'     => isset($matches['links']) ? $matches['links'] : 0
                );
            }

            // 6. Check if there is a next episode to be aired
            if(($q = $xpath->query($expressions->next_episode)) instanceof DOMNodeList 
                && $q->length && strtolower($q->item(0)->nodeValue) !== 'finished')
            {
                // 6.1 Get episode date
                $next_ep_date = $xpath->evaluate('string('.$expressions->next_episode.'/text()[1])');

                // Check if it was a date, if so format the date
                if(preg_match('/:.*?(?P<date>\d{2}\s.*\s\d{4})/i', $next_ep_date, $matches)):
                    $next_ep_date = $this->format_date($matches['date']);
                else:
                    $next_ep_date = null;
                endif;

                // 6.2 Get episode name & code
                $next_ep_data = $xpath->evaluate('string('.$expressions->next_episode.'/text()[2])');

                // Extract the name and code
                if(preg_match('/(?P<code>.*)\s-\s(?P<name>.*)/i', trim($next_ep_data), $matches))
                {
                    $next_ep_name = $matches['name'];
                    $next_ep_code = $matches['code'];
                }

                // Add to next episode array
                $next_episode[] = array(
                    'name'  => isset($next_ep_name) ? $next_ep_name : null,
                    'code'  => isset($next_ep_code) ? $next_ep_code : null,
                    'date'  => $next_ep_date
                );
            }

            // Check we have data to return, if not they probably got the wrong IDs
            if($title == null && empty($categories))
                throw new Exception('The TV show with the id "'.$show_id.'" was not found.', 1);

            // Return the response array
            return array(
                'title'         => $title,
                'plot'          => $plot,
                'image_url'     => $image_url,
                'trailers'      => $trailers,
                'next_episode'  => $next_episode,
                'categories'    => $categories
            );
        }
        catch(Exception $e)
        {
            // Call custom error handler function
            $this->handle_error($e);

            // Return the error object
            return $e;
        }
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