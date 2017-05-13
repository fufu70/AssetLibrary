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
     * Removes the basic environment for testing the File class
     */
    public static function tearDownAfterClass()
    {
        try {
            system("rm -rf ".escapeshellarg(current(explode('/', self::TEST_DIRECTORY))));
        } catch(\Exception $e) {}

        try {
            system("rm -rf ".escapeshellarg(current(explode('/', Action_Image::ROOT_DIR))));
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
            [
                $default_image_file,
                [],
                [
                    [
                        Action_Image::NAME_KEY   => "default_image",
                        Action_Image::WIDTH_KEY  => 1920,
                        Action_Image::HEIGHT_KEY => 1080
                    ],
                    [
                        Action_Image::NAME_KEY              => "default_image_contain",
                        Action_Image::WIDTH_KEY             => 1920,
                        Action_Image::HEIGHT_KEY            => 1080,
                        Action_Image::KEEP_ASPECT_RATIO_KEY => false,
                        Action_Image::PADDING_KEY           => true
                    ],
                    [
                        Action_Image::NAME_KEY              => 
                            "default_image_contain_aspect_true",
                        Action_Image::WIDTH_KEY             => 1920,
                        Action_Image::HEIGHT_KEY            => 1080,
                        Action_Image::KEEP_ASPECT_RATIO_KEY => true
                    ]
                ],
                [
                    "default_image"                     => 
                        self::COMPARISON_DIRECTORY . "/default_image.png",
                    "default_image_contain"             =>
                        self::COMPARISON_DIRECTORY . "/default_image_contain.png",
                    "default_image_contain_aspect_true" =>
                        self::COMPARISON_DIRECTORY . "/default_image_contain_aspect_true.png",
                ],
                "" // I dont expect an exception
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
                $this->assertFileExists($result[Action_Image::PATH_KEY]);

                list($r_width, $r_height) = getimagesize(
                    $result[Action_Image::PATH_KEY]
                );
                list($e_width, $e_height) = getimagesize(
                    $expected_results[$result[Action_Image::NAME_KEY]]
                );

                $this->assertEquals($r_width, $e_width);
                $this->assertEquals($r_height, $e_height);

                // cannot directly compare the files
                // $this->assertFileEquals(
                //     $result[Action_Image::PATH_KEY], 
                //     $expected_results[$result[Action_Image::NAME_KEY]]
                // );
            }
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), $exception_message);
        }
    }
}