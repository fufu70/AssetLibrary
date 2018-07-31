<?php

/**
 * Contains the File_Test class.
 * 
 * @package Asset\Test\File
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Test\File;

use Asset\Action\Document as Action_Document;
use Asset\File\Document as File_Document;
use Common\File;
use Common\File\NotFoundException;
use Common\File\NotSafeException;
use Common\File\NotValidException;

/**
 * File_Test class. A PHPUnit Test case class.
 *
 * Confirms the usage of the File_Document class.
 * 
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */

class Document_Test extends \PHPUnit_Framework_TestCase
{

    const TEST_DIRECTORY = 'File_Document_Test';
    const COMPARISON_DIRECTORY = 'test_comparison';

    /**
     * Sets up a basic environment with the testing directory
     */
    public function setUp()
    {
        try {
            $this->createDirectory(self::TEST_DIRECTORY);
        } catch(\Exception $e) {}
    }

    /**
     * Goes to create the entire dummy directory.
     *
     * @param string $directory The directory to actually create.
     */
    private function createDirectory($directory)
    {
        $dir_array = explode('/', $directory);
        $dir_current_arr = [];

        foreach ($dir_array as $key => $string) {
            $dir_current_arr[] = $string;
            $dir_str = implode('/', $dir_current_arr);
            if (!file_exists($dir_str))
                mkdir($dir_str);
        }
    }


    /**
     * Removes the basic environment for testing the Document class
     */
    public static function tearDownAfterClass()
    {
        try {
            system("rm -rf ".escapeshellarg(current(explode('/', self::TEST_DIRECTORY))));
        } catch(\Exception $e) {}

        try {
            system("rm -rf ".escapeshellarg(current(explode('/', Action_Document::ROOT_DIR))));
        } catch(\Exception $e) {}
    }

    /**
     *
     *
     *
     * Input 
     *
     *
     * 
     */

    /**
     * Creates files with valid types and invalid types.
     * 
     * @return array An array of file paths, valid types, and expected
     *               exception messages.
     */
    public function input_forge()
    {
        require_once(__DIR__ . "/../../src/ActionStrategy.php");
        require_once(__DIR__ . "/../../src/FileStrategy.php");
        require_once(__DIR__ . "/../../src/action/Document.php");
        require_once(__DIR__ . "/../../src/file/Document.php");
        require_once(__DIR__ . "/../../vendor/fufu70/file-class/src/file/NotSafeException.php");
        require_once(__DIR__ . "/../../vendor/fufu70/file-class/src/file/NotFoundException.php");
        require_once(__DIR__ . "/../../vendor/fufu70/file-class/src/file/NotValidException.php");

        $this->createDirectory(self::TEST_DIRECTORY);

        $not_valid_file  = self::TEST_DIRECTORY . "/not_valid.txt";
        file_put_contents($not_valid_file, "This file is simply a text file(filename)");

        $valid_txt = self::TEST_DIRECTORY . "/valid.txt";
        file_put_contents($valid_txt, "Hello World");

        $valid_csv = self::TEST_DIRECTORY . "/valid.csv";
        file_put_contents($valid_csv, "Title,Description\nHello,World");

        $valid_xlsx = self::TEST_DIRECTORY . "/valid.xlsx";
        file_put_contents($valid_xlsx, "Hello World");

        return [
            [
                self::TEST_DIRECTORY,
                [],
                [],
                [],
                NotSafeException::FILE_NOT_SAFE
            ],
            [
                self::TEST_DIRECTORY . '/doesnotexist.txt',
                [],
                [],
                [],
                NotFoundException::FILE_NOT_FOUND
            ],
            [
                $not_valid_file,
                [],
                [],
                [],
                NotValidException::FILE_NOT_VALID
            ],
            [
                $valid_txt,
                [],
                [
                    [
                        Action_Document::NAME_KEY => "valid_txt"
                    ]
                ],
                [
                    "valid_txt" => 
                        self::COMPARISON_DIRECTORY . "/valid_txt.zip",
                ],
                "" // I dont expect an exception
            ],
            [
                $valid_csv,
                [],
                [
                    [
                        Action_Document::NAME_KEY =>  "valid_csv"
                    ]
                ],
                [
                    "valid_csv" =>
                        self::COMPARISON_DIRECTORY . "/valid_csv.zip",
                ],
                "" // I dont expect an exception
            ],
            [

                $valid_xlsx,
                [],
                [
                    [
                        Action_Document::NAME_KEY => "valid_xlsx"
                    ]
                ],
                [
                    "valid_xlsx" => 
                        self::COMPARISON_DIRECTORY . "/valid_xlsx.zip",
                ],
                "" // I dont expect an exception
            ]
        ];
    }

    /**
     *
     *
     *
     * Test
     *
     *
     *
     */

    /**
     * Tests the forge.
     *
     * @dataProvider input_forge
     *
     * @param  string  $path              The file path.
     * @param  array   $valid_types       The types to validate against.
     * @param  array   $action            The actions to pass.
     * @param  array   expected_results   The key, the action name, and a file of
     *                                    the expected action.
     * @param  string  $exception_message The expected exception message if an
     *                                    excption is thrown.
     */
    public function test_forge(
        $path = "",
        array $valid_types = [],
        array $actions = [],
        array $expected_results = [],
        $exception_message = ""
    ) {
        try {
            $results = File_Document::forge($path, $valid_types, $actions)->act();

            foreach ($results as $result) {
                $this->assertFileExists($result[Action_Document::PATH_KEY]);
            }
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $exception_message);
        }
    }
}