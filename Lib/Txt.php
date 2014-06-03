<?php
namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class to access SMF $txt in a lazy way
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Txt
{
    /**
     * Get text from SMF $txt array.
     *
     * @param string $key
     * @param string $app
     * @return string From many places all over the framework you have access to this method by $this->txt().
     *         These are every controller, every model and all other classes which relay on the Lib class
     *         or are children of classes that are using it.
     *         The lazy typing approach in mind, this method adds missing key data and builds app related
     *         key strings when an app name is set.
     *
     *         If no text is found for the key, the key itself will be returned as text. This makes it much
     *         easier to find and create missing translations.
     */
    public static function get($key, $app = null)
    {
        global $txt;

        // IMPORTANT! Keys with spaces won't be processed without any further
        // notice to the developer or user. Spaces mean texts and no keys for the $txt array.
        if (strpos($key, ' '))
            return $key;

            // None web app texts
        if (isset($app) && $app == 'SMF')
            return isset($txt[$key]) ? $txt[$key] : $key;

            // Extend web related key with 'app_' string - if needed
        if (substr($key, 0, 4) == 'web_')
            $key = 'app_' . $key;

            // A set app name means we have to create the web apps specific txt key
            // which has to look like "app_appname_key" in the language file.
        elseif (substr($key, 0, 4) !== 'web_' && !strpos($app, 'app_') && isset($app))
            $key = 'app_' . String::uncamelize($app) . '_' . $key;

        if (isset($txt[$key]))
            return $txt[$key];
        else
        {
            // If no text is found, the requested text key will be returned.
            // This is much more easier for devs than simply to show nothing
            // like SMF normally does.
            return $key;
        }
    }
}
?>
