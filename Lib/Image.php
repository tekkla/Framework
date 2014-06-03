<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

// Used classes
use Web\Framework\Lib\Abstracts\ClassAbstract;

/**
 * Class for working wirth imanges
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 * @todo Very rudimental for now. Used this only for an app.
 *       Needs to be more complex and maybe it is a good idea to
 *       make an object of http request it in the future.
 * @deprecated This is old. Use SimpleImage lib instead.
 */
class Image extends ClassAbstract
{

    public static function resize($img, $new_filename, $new_width, $new_height = null, $jpeg_qualitiy = 100)
    {
        $max_width = $new_width;

        // Check if GD extension is loaded
        if (!extension_loaded('gd') && !extension_loaded('gd2'))
            Throw new Error("GD is not loaded");

            // Get Image size info
        list($width_orig, $height_orig, $image_type) = getimagesize($img);

        switch ($image_type)
        {
            case 1 :
                $im = imagecreatefromgif($img);
                break;
            case 2 :
                $im = imagecreatefromjpeg($img);
                break;
            case 3 :
                $im = imagecreatefrompng($img);
                break;
            default :
                return false;
                break;
        }

        // calculate the aspect ratio
        $aspect_ratio = (float) $height_orig / $width_orig;

        // calulate the thumbnail width based on the height
        if (!isset($new_height))
        {
            $new_height = round($new_width * $aspect_ratio);

            while ( $new_height > $max_width )
            {
                $new_width -= 10;
                $new_height = round($new_width * $aspect_ratio);
            }
        }

        $new_img = imagecreatetruecolor($new_width, $new_height);

        // Check if this image is PNG or GIF, then set if Transparent
        if (($image_type == 1) || ($image_type == 3))
        {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
            $transparent = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
            imagefilledrectangle($new_img, 0, 0, $new_width, $new_height, $transparent);
        }
        imagecopyresampled($new_img, $im, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);

        // Generate the file, and rename it to $new_filename
        switch ($image_type)
        {
            case 1 :
                imagegif($new_img, $new_filename);
                break;
            case 2 :
                imagejpeg($new_img, $new_filename, $jpeg_qualitiy);
                break;
            case 3 :
                imagepng($new_img, $new_filename, 0);
                break;
            default :
                return false;
                break;
        }

        return $new_filename;
    }
}

?>