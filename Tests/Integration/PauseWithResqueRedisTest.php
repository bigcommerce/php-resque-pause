<?php
namespace Resque\Plugins\Tests\Integration;

use Resque;

/**
 * Class PauseWithResqueRedisTest Runs the PauseTestBase cases with a Resque_Redis backend
 * @package Resque\Plugins\Tests\Integration
 */
class PauseWithResqueRedisTest extends PauseTestBase
{
    public static function setUpBeforeClass()
    {
        static::setupRedis();
        Resque::setBackend('localhost:' . static::getRedisPort());
    }
}
