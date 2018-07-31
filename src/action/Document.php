<?php

/**
 * Contains the Document class
 *
 * @package Asset\Action
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Action;

use Asset\ActionStrategy;

/**
 * Document class.
 *
 * Alters a given file according to the given configurations.
 *
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */
class Document implements ActionStrategy
{
    const ROOT_DIR = 'tmp_action/';

    // Action and Result Keys
    const PATH_KEY = 'path';
    const NAME_KEY = 'name';

    // Action Defaults
    const NAME_DEFAULT = 'default_name';

    public $_path;
    public $_action;
    public $_action_path;

    private static $_default_action = [
        self::NAME_KEY => self::NAME_DEFAULT,
    ];

    /**
     * Creates an instance of the Document Action and returns it.
     *
     * @param  string $file_path The path to the file.
     * @param  array  $action    The action itself, what to do on the file
     * @return Document              An instance of itself.
     */
    public static function forge($file_path = "", array $action = [])
    {
        return new self($file_path, $action);
    }

    /**
     * Document constructor.
     *
     * @param string $file_path The path to the file.
     * @param array  $action    The action itself, what to do on the file
     */
    public function __construct($file_path = "", array $action = [])
    {
        $this->_path = $file_path;
        $this->_action = $action + self::$_default_action;
        $this->_action_path = $this->_getActionPath();
    }

    /**
     * Sets the final path from the local file basename, manipulation name.
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
        $basename .= '-' . $this->_action[self::NAME_KEY] . '.zip';

        // setup path
        $path = self::ROOT_DIR . $basename;

        // create the file
        if (!file_exists(self::ROOT_DIR)) {
            mkdir(self::ROOT_DIR);
        }

        return $path;
    }

    /**
     * Manipulate the File and store it as a zip.
     * 
     * @return array Contains the location of the result of the action, its
     *               a zip file, and the name of the resulting action.
     */
    public function act()
    {
        $this->zip();

        return [
            self::PATH_KEY => $this->_action_path,
            self::NAME_KEY => $this->_action[self::NAME_KEY]
        ];
    }

    /**
     * Zips up the given file in the _action_path.
     * 
     * Zips up the the current file by opening up a ziparchive, adding the
     * file to the archive and then closing the zip.
     * 
     * @return bool If the zip was produced.
     */
    private function zip() 
    {
        $zip = new \ZipArchive();
        if($zip->open($this->_action_path, \ZipArchive::CREATE) !== true) {
            return false;
        }

        $zip->addFile($this->_path, $this->_path);
        $zip->close();

        return file_exists($this->_action_path);
    }
}