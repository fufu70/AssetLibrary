<?php

/**
 * Contains the Image class 
 *
 * @package Asset\Action
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Action;

use Asset\ActionStrategy;

/**
 * Image class.
 *
 * Alters a given image according to the given configurations.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
class Image implements ActionStrategy
{

    const MAX_COMPRESSION = 9;
    const MIN_COMPRESSION = 0;
    const ROOT_DIR        = 'tmp_action/';
    const MAX_SIZE        = 5000000;
    const MAX_QUALITY     = 100;

    // Action and Result Keys
    const PATH_KEY              = 'path';
    const NAME_KEY              = 'name';
    const HEIGHT_KEY            = 'height';
    const WIDTH_KEY             = 'width';
    const KEEP_ASPECT_RATIO_KEY = 'keep_aspect_ratio';
    const PADDING_KEY           = 'padding';

    // Action Defaults
    const NAME_DEFAULT              = 'default_name';
    const HEIGHT_DEFALT             = 1000;
    const WIDTH_DEFAULT             = 1000;
    const KEEP_ASPECT_RATIO_DEFAULT = null;
    const PADDING_DEFAULT           = true;

    private $_action;
    private $_action_path;
    private $_path;
    private $_quality;
    private $_imagick_image;

    private static $_default_action = [
        self::NAME_KEY              => self::NAME_DEFAULT,
        self::HEIGHT_KEY            => self::HEIGHT_DEFALT,
        self::WIDTH_KEY             => self::WIDTH_DEFAULT,
        self::KEEP_ASPECT_RATIO_KEY => self::KEEP_ASPECT_RATIO_DEFAULT,
        self::PADDING_KEY           => self::PADDING_DEFAULT,
    ];

    /**
     * Creates an instance of the Image Action and returns it.
     *
     * @param  string $image_path The path to the image.
     * @param  array  $action     The action itself, what to do on the image
     * @return Image              An instance of itself.
     */
    public static function forge($image_path = "", array $action = []) 
    {
        return new self($image_path, $action);
    }

    /**
     * Image constructor.
     *
     * @param string $image_path The path to the image.
     * @param array  $action     The action itself, what to do on the image
     */
    public function __construct($image_path = "", array $action = []) 
    {
        $this->_path          = $image_path;
        $this->_action        = $action + self::$_default_action;
        $this->_action_path   = $this->_getActionPath();
        $this->_quality       = self::MAX_QUALITY;
        $this->_imagick_image = new \Imagick($this->_path);

        $this->_cleanWidthAndHeight();
    }

    /**
     * Sets the final path from the local image basename, manipulation name.
     *
     * Creates a file path from the name of the given file, the current path of that
     * file, the current timestamp of the machine, and a random number between 0 - 1000.
     * This allows the final path to be unique. After the path is decided it then
     * tries to create that path image by touching it.
     *
     * @return string The location of the file where the actions are applied.
     */
    private function _getActionPath() 
    {
        // setup the basename
        $basename = explode('.', basename($this->_path))[0];
        $basename .= '-' . time() . '-' . rand(0, 1000);
        $basename .= '-' . $this->_action[self::NAME_KEY] . '.png';

        // setup path
        $path = self::ROOT_DIR . $basename;

        // create the file
        if (!file_exists(self::ROOT_DIR)) {
            mkdir(self::ROOT_DIR);
        }

        touch($path);

        return $path;
    }

    /**
     * Cleans up the width and height values.
     *
     * When the width or height value is zero we assume that the original width
     * or height of the image should be used.
     */
    private function _cleanWidthAndHeight() 
    {
        list($original_width, $original_height) = getimagesize($this->_path);

        $this->_action[self::WIDTH_KEY] = ($this->_action[self::WIDTH_KEY] !== 0) ?
            $this->_action[self::WIDTH_KEY] : $original_width;
        $this->_action[self::HEIGHT_KEY] = ($this->_action[self::HEIGHT_KEY] !== 0) ?
            $this->_action[self::HEIGHT_KEY] : $original_height;
    }

    /**
     * Manipulate the image.
     *
     * Acts on the image given the action information, formats it accordingly,
     * and compresses it so as to limit its maximum size.
     *
     * @return array Contains the location of the result of the action, its
     *               another image, and the name of the resulting action.
     */
    public function act() 
    {
        $this->_manipulate();
        $this->_imagick_image->writeImage();

        return [
            self::PATH_KEY => $this->_action_path,
            self::NAME_KEY => $this->_action[self::NAME_KEY]
        ];
    }

    /**
     * Manipulates the image based off of the action given.
     *
     * Converts, Compresses, and Formats the image according to the action information
     * given about its conversion and compression / formatting after conversion.
     */
    private function _manipulate() 
    {
        $this->_format();
        $this->_autorotate();
        $this->_convert();
        $this->_compress();
    }

    /**
     * Auto orientate the image to the proper size.
     */
    private function _autorotate() 
    {
        switch ($this->_imagick_image->getImageOrientation()) {
            case \Imagick::ORIENTATION_TOPLEFT:
                break;
            case \Imagick::ORIENTATION_TOPRIGHT:
                $this->_imagick_image->flopImage();
                break;
            case \Imagick::ORIENTATION_BOTTOMRIGHT:
                $this->_imagick_image->rotateImage("#000", 180);
                break;
            case \Imagick::ORIENTATION_BOTTOMLEFT:
                $this->_imagick_image->flopImage();
                $this->_imagick_image->rotateImage("#000", 180);
                break;
            case \Imagick::ORIENTATION_LEFTTOP:
                $this->_imagick_image->flopImage();
                $this->_imagick_image->rotateImage("#000", 270);
                break;
            case \Imagick::ORIENTATION_RIGHTTOP:
                $this->_imagick_image->rotateImage("#000", 90);
                break;
            case \Imagick::ORIENTATION_RIGHTBOTTOM:
                $this->_imagick_image->flopImage();
                $this->_imagick_image->rotateImage("#000", 90);
                break;
            case \Imagick::ORIENTATION_LEFTBOTTOM:
                $this->_imagick_image->rotateImage("#000", 270);
                break;
            default: // Invalid orientation
                break;
        }

        $this->_imagick_image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
    }

    /**
     * Converts the image given the current action.
     *
     * Alters the given image by width, height, padding, and keep aspect ratio. If
     * the keep aspect ratio is not null then it contains the image inside of the
     * given width and height, otherwise it covers the width and height with the image
     * and crops to that width and height.
     */
    private function _convert() 
    {
        if (!is_null($this->_action[self::KEEP_ASPECT_RATIO_KEY]) &&
            !is_null($this->_action[self::PADDING_KEY])) {
            $this->_contain();
        } else {
            $this->_cover();
        }
    }

    /**
     * Manipulates the image to fill in the entire width and height of an image.
     *
     * Utilizes imagick to create a thumbnail image from the given width and height
     * of the action. This allows the entire space of the width and hight to contain
     * the image, with no filler.
     */
    private function _cover() 
    {
        $this->_imagick_image->setImageBackgroundColor('transparent');
        $this->_imagick_image->cropThumbnailImage(
            $this->_action[self::WIDTH_KEY],
            $this->_action[self::HEIGHT_KEY]
        );

        $this->_imagick_image->writeImage($this->_action_path);
    }

    /**
     * Applies padding to an image to fill its width and height.
     *
     * Creates a thumbnail image from the given 
     */
    private function _contain() 
    {
        $this->_imagick_image->setImageBackgroundColor('#000'); // black

        if (!$this->_action[self::KEEP_ASPECT_RATIO_KEY]) {
            $this->_imagick_image->thumbnailImage(
                $this->_action[self::WIDTH_KEY],
                $this->_action[self::HEIGHT_KEY],
                true,
                $this->_action[self::PADDING_KEY]
            );
        } else {
            $this->_imagick_image->scaleImage(
                $this->_action[self::WIDTH_KEY],
                $this->_action[self::HEIGHT_KEY],
                true
            );
        }

        $this->_imagick_image->writeImage($this->_action_path);
    }

    /**
     * Formats the image into a png.
     *
     * Creates a png from the current image and tries to keep its current alpha
     * value.
     */
    private function _format() 
    {
        $this->_imagick_image->stripImage(); // if you want to get rid of all EXIF data
        $this->_imagick_image->setImageFormat("png");
        $this->_imagick_image->setImageCompressionQuality($this->_quality);
    }

    /**
     * Goes to compress the image.
     *
     * Compresses the Image if it is over the MAX_SIZE of bytes of an image. It
     * calls the Manipulate function again and reduces the quality to get a smaller
     * image.
     */
    private function _compress() 
    {
        clearstatcache();
        if (filesize($this->_action_path) > self::MAX_SIZE) {
            $this->_quality -= 10;
            if ($this->_quality > 10) {
                $this->_manipulate();
            }
        }
    }
}