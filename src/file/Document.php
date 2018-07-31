<?php

/**
 * Contains the Document class
 *
 * @package Asset\File
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\File;

use Asset\Action\Document as Document_Action;
use Asset\FileStrategy;
use Common\File;
use Common\File\NotFoundException;
use Common\File\NotSafeException;
use Common\File\NotValidException;

/**
 * Document class.
 *
 * Creates file files based off of the given manipulations.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
class Document implements FileStrategy
{
    /**
     * FileStrategy constructor.
     *
     * @param string $file_path   The location of the file
     * @param array  $valid_types The file types to accept.
     * @param array  $actions     The actions to utilize.
     * @return Document               An instance of its self.
     */
    public static function forge(
        $file_path = "",
        array $valid_types = [],
        array $actions = []
    )
    {
        return new self($file_path, $valid_types, $actions);
    }

    /**
     * Document Constructor.
     *
     * @param string $file_path   The location of the file
     * @param array  $valid_types The file types to accept.
     * @param array  $actions     The actions to utilize.
     * @throws NotFoundException
     * @throws NotSafeException
     * @throws NotValidException
     */
    public function __construct(
        $file_path = "",
        array $valid_types = [],
        array $actions = []
    ) {
        File::usable($file_path, $valid_types); // does not throw an exception.

        $this->_path    = $file_path;
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
        $file_results = [];

        foreach ($this->_actions as $action) {
            $file_results[] = Document_Action::forge($this->_path, $action)->act();
        }

        return $file_results;
    }
}