<?php
/**
 * Contains the FileStrategy interface used to create assets from a file.
 *
 * @package Asset
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset;

/**
 * Interface FileStrategy.
 *
 * Creates assets from a the given file.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
interface FileStrategy
{

    /**
     * FileStrategy constructor.
     *
     * @param string $file_path   The location of the file
     * @param array  $valid_types The file types to accept.
     * @param array  $actions     The actions to utilize.
     * @return Image              An instance of its self.
     */
    public static function forge(
        $file_path = "",
        array $valid_types = [],
        array $actions = []
    );


    /**
     * Goes to act on the file to change it.
     *
     * @return array An array of arrays that contain the result_name and
     *               its path.
     */
    public function act();
}