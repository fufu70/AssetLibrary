<?php

/**
 * Contains the Image class 
 *
 * @package Asset\Action
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Action;

use Asset\Action_Strategy;

/**
 * Image class.
 *
 * Alters a given image according to the given configurations.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
class Image implements Action_Strategy
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
    const PADDING_DEFAULT           = null;

    private $_action;
    private $_action_path;
    private $_path;
    private $_quality;

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
    public function forge($image_path = "", array $action = []) {
        return new self($image_path, $action);
    }

    /**
     * Image constructor.
     *
     * @param string $image_path The path to the image.
     * @param array  $action     The action itself, what to do on the image
     */
    public function __construct($image_path = "", array $action = []) {
        $this->_path        = $image_path;
        $this->_action      = $action + self::$_default_action;
        $this->_action_path = $this->_getActionPath();
        $this->_quality     = self::MAX_QUALITY;
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
    private function _getActionPath() {
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
     * Manipulate the image.
     *
     * Acts on the image given the action information, formats it accordingly,
     * and compresses it so as to limit its maximum size.
     * 
     * @return array Contains the location of the result of the action, its 
     *               another image, and the name of the resulting action.
     */
    public function act() {


        return [
            self::PATH_KEY => $this->_action_path,
            self::NAME_KEY => $this->_action[self::NAME_KEY]
        ];
    }

    /**
     * Formats the image into a png.
     *
     * Creates a png from the current image and tries to keep its current alpha
     * value.
     */
    private function _format() {
        $src = imagecreatefrompng($this->_action_path);

        imagealphablending($src, true);
        imagesavealpha($src, true);
        imagepng($src, $this->_action_path, $this->_getCompressionLevel());
    }

    /**
     * Returns the compression level for the desired quality.
     *
     * Converts the quality level from a range of 100 - 0 to a compression level
     * of 0 - 9. "How much" do we want to compress the image based off of the current
     * quality; the higher the quality, the lower the compression.
     *
     * @return int The compression level from the current quality
     */
    private function _getCompressionLevel() {
        $compression = self::MIN_COMPRESSION;

        $quality_rev = 100 - $this->_quality;
        if ($quality_rev >= 10) {
            $compression = intval($quality_rev / 10);
        }

        if ($compression > self::MAX_COMPRESSION) {
            $compression = self::MAX_COMPRESSION;
        }

        return $compression;
    }
}