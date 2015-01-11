<?php
/**
 * Example.com parser
 * 
 * An example of a video host parser. The class must have the same name as the host
 * name with the tld at the end
 * 
 * E.g example.com would be class example_com  or demo.com would be demo_com.
 * 
 * @package     PFTV
 * @subpackage  parsers
 * @access      public
 * @version     1.0
 * @author 		Ben Thomson <bathomson93@gmail.com>
 */
class example_com extends host_parser
{
	/**
	 * Extract the link from example.com
	 * @param 	string 		$embedded_url 	The embedded url that was on the PFTV website
	 * @return 	string|null 	Direct link of the video or null
	 */
	public static function get_link($embedded_url)
    {
        // Get the source
        $html_source = file_get_contents($embedded_url);

        // Match the url
        preg_match('/file:.?"(?P<url>.*?)"/i', $html_source, $matches);

        // Return the url
        return isset($matches['url']) ? $matches['url'] : null;
    }
}