<?php
/**
 * Contains the ActionStrategy interface used to define the action pipeline.
 *
 * @package Asset
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset;

/**
 * Interface ActionStrategy.
 *
 * Acts on a given file with given parameters.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
interface ActionStrategy
{
    /**
     * ActionStrategy constructor.
     *
     * @param string $file_path The path to the file.
     * @param array  $action    The action itself, what to do on the file
     */
    public function __construct($file_path = "", array $action = []);

    /**
     * Goes to act on the file to change it.
     *
     * @return array The result_name and the path of the action.
     */
    public function act();
}