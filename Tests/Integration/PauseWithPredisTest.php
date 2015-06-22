<?php
namespace Resque\Plugins\Tests\Integration;

use Resque;
use Predis\Client;

/**
 * Class PauseWithPredisTest Runs the PauseTestBase cases with a Predis backend
 * @package Resque\Plugins\Tests\Integration
 */
class PauseWithPredisTest extends PauseTestBase
{
    public static function setUpBeforeClass()
    {
        static::setupRedis();

        $clientParams = array(
            'host' => 'localhost',
            'port' => static::getRedisPort(),
            'prefix' => 'resque:',
        );
        Resque::setBackend(function () use ($clientParams) {
            return new Client($clientParams);
        });
    }
}
