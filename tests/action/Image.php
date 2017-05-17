<?php

/**
 * Contains the Image_Test class.
 * 
 * @package Asset\Test\Action
 * @author  Christian Micklisch <christian.micklisch@successwithsos.com>
 */

namespace Asset\Test\Action;

use Asset\Action\Image as Action_Image;
use Common\Reflection;

/**
 * Image_Test class. A PHPUnit Test case class.
 *
 * Tests specific functions inside of the Action\Image class.
 * 
 * @author Christian Micklisch <christian.micklisch@successwithsos.com>
 */

class Image_Test extends \PHPUnit_Framework_TestCase
{

    const TEST_DIRECTORY = 'Asset_Image_Test';

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
     * @return array An array of Orientation
     */
    public function input_autorotate()
    {
        return [
            [\Imagick::ORIENTATION_TOPLEFT],
            [\Imagick::ORIENTATION_TOPRIGHT],
            [\Imagick::ORIENTATION_BOTTOMRIGHT],
            [\Imagick::ORIENTATION_BOTTOMLEFT],
            [\Imagick::ORIENTATION_LEFTTOP],
            [\Imagick::ORIENTATION_RIGHTTOP],
            [\Imagick::ORIENTATION_RIGHTBOTTOM],
            [\Imagick::ORIENTATION_LEFTBOTTOM]
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
     * @dataProvider input_autorotate
     *
     * @param  string  $path              The file path.
     * @param  array   $valid_types       The types to validate against.
     * @param  array   $action            The actions to pass.
     * @param  array   expected_results   The key, the action name, and a file of
     *                                    the expected action.
     * @param  string  $exception_message The expected exception message if an
     *                                    excption is thrown.
     */
    public function test_autorotate(
        $imagick_orientation = \Imagick::ORIENTATION_TOPLEFT
    ) {
        $dummy_file = self::TEST_DIRECTORY . "dummy.png";
        file_put_contents($dummy_file, file_get_contents("http://placehold.it/350x150"));

        $imagick = new \Imagick(realpath($dummy_file));
        $imagick->setImageOrientation($imagick_orientation);

        $image = new Action_Image($dummy_file);

        Reflection::setProperty('_imagick_image', 'Asset\Action\Image', $image, $imagick);

        try {
            Reflection::callMethod('_autorotate', 'Asset\Action\Image', [], $image);
            $action_imagick_image = Reflection::getProperty('_imagick_image', 'Asset\Action\Image', $image);

            $this->assertEquals(
                \Imagick::ORIENTATION_TOPLEFT,
                $action_imagick_image->getImageOrientation()
            );
        } catch (\Exception $e) {
            $this->assertEquals(false, true, $e->getMessage());
        }
    }
}