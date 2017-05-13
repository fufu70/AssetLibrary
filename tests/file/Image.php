<?php

/**
 * Contains the Image_Test class.
 * 
 * @package Asset\Test\File
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Test\File;

use Asset\Action\Image as Action_Image;
use Asset\File\Image as File_Image;
use Common\File;
use Common\File\NotFoundException;
use Common\File\NotSafeException;
use Common\File\NotValidException;

/**
 * Image_Test class. A PHPUnit Test case class.
 *
 * Confirms the usage of the File_Image class.
 * 
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */

class Image_Test extends \PHPUnit_Framework_TestCase
{

    const TEST_DIRECTORY = 'File_Image_Test';

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
     * Removes the basic environment for testing the File class
     */
    public static function tearDownAfterClass()
    {
        try {
            system("rm -rf ".escapeshellarg(current(explode('/', self::TEST_DIRECTORY))));
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
        $this->createDirectory(self::TEST_DIRECTORY);

        $default_image_file = self::TEST_DIRECTORY . "/usable.png";
        file_put_contents($default_image_file, file_get_contents("http://placehold.it/350x150"));

        $not_valid_file  = self::TEST_DIRECTORY . "/not_valid.txt";
        file_put_contents($not_valid_file, "This file is simply a text file(filename)");

        return [
            [
                $default_image_file,
                [],
                [],
                [],
                "" // I dont expect an exception
            ],
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
            $results = File_Image::forge($path, $valid_types, $actions)->act();

            foreach ($results as $result) {
                $this->assertFileEquals(
                    $result[Action_Image::PATH_KEY], 
                    $expected_results[$results[Action_Image::NAME_KEY]]
                );
            }
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $exception_message);
        }
    }
}