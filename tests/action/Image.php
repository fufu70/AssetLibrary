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
    const DIRECTORY = 'Action_Image_Test';
    const COMPARISON_DIRECTORY = 'test_comparison';


    /**
     * Creates a directory
     */
    public function setUp()
    {
        $this->_create_directory();
    }

    /**
     * Creates the constant directory.
     */
    private function _create_directory()
    {
        // create the directory
        if (!file_exists(self::DIRECTORY)) {
            mkdir(self::DIRECTORY);
        }
    }

    /**
     * Removes the test suite directories, including the root directory for manipulate.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        try {
            system("rm -rf ".escapeshellarg(current(explode('/', Action_Image::ROOT_DIR))));
        } catch(\Exception $e) {}

        try {
            system("rm -rf ".escapeshellarg(current(explode('/', self::DIRECTORY))));
        } catch(\Exception $e) {}
    }

    /**
     *
     *
     * Input Functions
     *
     *
     *
     */

    /**
     * A slew of test inputs for checking that the compression level is created properly
     * from the quality given.
     */
    public function input_compression_level()
    {
        return [
            [
                'quality'     => 100,
                'compression' => 0
            ],
            [
                'quality'     => 0,
                'compression' => 9
            ],
            [
                'quality'     => 110,
                'compression' => 0
            ],
            [
                'quality'     => -100,
                'compression' => 9
            ],
            [
                'quality'     => -110,
                'compression' => 9
            ],
            [
                'quality'     => 'a',
                'compression' => 9
            ]
        ];
    }

    /**
     *
     *
     *
     * Test Functions
     *
     *
     */

    /**
     * Tests that the compression level function operates properly by checking that
     * the created image with the desired quality selects the proper compression level.
     * This function is mainly there for the formatting operation.
     *
     * @dataProvider input_compression_level
     *
     * @param string $quality The quality of the desired image
     * @param integer $compression_level The level of compression based off of the
     *                                   quality.
     */
    public function test_get_compression_level($quality = 100, $compression_level = 0)
    {
        // dummy file to not throw errors
        $dummy_file = self::DIRECTORY . "dummy.png";
        file_put_contents($dummy_file, file_get_contents("http://placehold.it/350x150"));

        $image = new Action_Image($dummy_file);
        Reflection::setProperty('_quality', 'Asset\Action\Image', $image, $quality);

        $this->assertEquals(
            $compression_level,
            Reflection::callMethod('_getCompressionLevel', 'Asset\Action\Image', [], $image)
        );
    }
}