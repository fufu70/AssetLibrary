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

    const PATH_KEY = 'path';
    const NAME_KEY = 'name';

    private $_path;
    private $_action;

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
        $this->_path   = $image_path;
        $this->_action = $action;
    }

    /**
     * Manipulate the image.
     * 
     * @return array Contains the location of the result of the action, its 
     *               another image, and the name of the resulting action.
     */
    public function act() {
        return [];
    }
}