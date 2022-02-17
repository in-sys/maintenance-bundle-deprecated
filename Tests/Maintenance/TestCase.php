<?php

namespace INSYS\Bundle\MaintenanceBundle\Tests\Maintenance;

/**
 *
 *
 * @package INSYSMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    static protected $files;

    public static function setUpBeforeClass(): void
    {
        $tmpDir = sys_get_temp_dir().'/symfony2_finder';
        self::$files = array(
            $tmpDir.'/lock.lock',
        );

        if (is_dir($tmpDir)) {
            self::tearDownAfterClass();
        } else {
            mkdir($tmpDir);
        }

        foreach (self::$files as $file) {
            if ('/' === $file[strlen($file) - 1]) {
                mkdir($file);
            } else {
                @touch($file);
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        foreach (array_reverse(self::$files) as $file) {
            if ('/' === $file[strlen($file) - 1]) {
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
    }
}
