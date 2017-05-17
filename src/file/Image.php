<?php

/**
 * Contains the Image class
 *
 * @package Asset\File
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\File;

use Asset\Action\Image as Image_Action;
use Asset\FileStrategy;
use Common\File;
use Common\File\NotFoundException;
use Common\File\NotSafeException;
use Common\File\NotValidException;

/**
 * Image class.
 *
 * Creates image files based off of the given manipulations.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
class Image implements FileStrategy
{

    private $_path;
    private $_actions;

    /**
     * Creates an instance of the self and returns it
     *
     * @param string $image_path  The location of the image
     * @param array  $valid_types The image types to accept.
     * @param array  $actions     The actions to utilize.
     * @return Image              An instance of its self.
     */
    public static function forge(
        $image_path = "",
        array $valid_types = [],
        array $actions = []
    ) {
        return new self($image_path, $valid_types, $actions);
    }

    /**
     * Image Constructor.
     *
     * @param string $image_path  The location of the image
     * @param array  $valid_types The image types to accept.
     * @param array  $actions     The actions to utilize.
     * @throws NotFoundException
     * @throws NotSafeException
     * @throws NotValidException
     */
    public function __construct(
        $image_path = "",
        array $valid_types = [],
        array $actions = []
    ) {
        File::usable($image_path, $valid_types); // does not throw an exception.

        $this->_path    = $image_path;
        $this->_actions = $actions;
    }

    /**
     * Acts on the given actions and returns the result.
     * 
     * Goes through the list of actions and runs through each action to generate
     * a file.
     *
     * @return array An array of arrays that contain the result_name and its path.
     */
    public function act()
    {
        $image_results = [];

        foreach ($this->_actions as $action) {
            $image_results[] = Image_Action::forge($this->_path, $action)->act();
        }

        return $image_results;
    }
}