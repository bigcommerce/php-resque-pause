<?php
namespace Resque\Integration\Plugins\Tests;

use Resque;
use Resque\Plugins\Pause;
use PHPUnit_Framework_TestCase;

/**
 * Pause tests.
 *
 * @package     PHP Resque Pause
 * @author      Wedy Chainy <wedy.chainy@bigcommerce.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class PauseTest extends PHPUnit_Framework_TestCase
{
    /** @var Pause */
    protected $pauser = null;

    public static function setUpBeforeClass()
    {
        $testMisc = realpath(__DIR__ . '/misc/');
        $redisConf = "$testMisc/redis.conf";

        // Attempt to start our own redis instance for tesitng.
        exec('which redis-server', $output, $returnVar);
        if ($returnVar != 0) {
            echo "Cannot find redis-server in path. Please make sure redis is installed.\n";
            exit(1);
        }

        exec("cd $testMisc; redis-server $redisConf", $output, $returnVar);
        usleep(500000);
        if ($returnVar != 0) {
            echo "Cannot start redis-server.\n";
            exit(1);
        }

        // Get redis port from conf
        $config = file_get_contents($redisConf);
        if (!preg_match('#^\s*port\s+([0-9]+)#m', $config, $matches)) {
            echo "Could not determine redis port from redis.conf";
            exit(1);
        }

        Resque::setBackend('localhost:' . $matches[1]);
    }

    public function setUp()
    {
        Resque::redis()->flushAll();
        $this->pauser = new Pause();
    }

    public function testPause()
    {
        // Pause non-paused queue
        Resque::enqueue('upgrade:test', 'test');
        Resque::enqueue('upgrade:test', 'test');
        $this->assertEquals(2, Resque::size('upgrade:test'));
        $this->assertTrue($this->pauser->pause('upgrade:test'));
        $this->assertEquals(0, Resque::size('upgrade:test'));

        // Pause paused queue
        $this->assertTrue($this->pauser->pause('upgrade:test'));

        // Pause non-existent queue
        $this->assertTrue($this->pauser->pause('upgrade:test2'));
    }

    public function testResume()
    {
        // Resume paused queue
        Resque::enqueue('upgrade:test3', 'test');
        Resque::enqueue('upgrade:test3', 'test');
        $this->pauser->pause('upgrade:test3');
        $this->assertTrue($this->pauser->resume('upgrade:test3'));
        $this->assertEquals(2, Resque::size('upgrade:test3'));

        // Resume non-paused queue
        Resque::enqueue('upgrade:test4', 'test');
        $this->assertTrue($this->pauser->resume('upgrade:test4'));

        // Resume non-existent queue
        $this->assertTrue($this->pauser->resume('upgrade:idontexist'));
    }

    public function testIsPaused()
    {
        // Paused queue
        Resque::enqueue('upgrade:test5', 'test');
        $this->pauser->pause('upgrade:test5');
        $this->assertTrue($this->pauser->isPaused('upgrade:test5'));

        // Non-paused queue
        Resque::enqueue('upgrade:test6', 'test');
        $this->assertFalse($this->pauser->isPaused('upgrade:test6'));

        // Paused and resumed queue
        Resque::enqueue('upgrade:test7', 'test');
        $this->pauser->pause('upgrade:test7');
        $this->assertTrue($this->pauser->isPaused('upgrade:test7'));
        $this->pauser->resume('upgrade:test7');
        $this->assertFalse($this->pauser->isPaused('upgrade:test7'));

        // Non-existent queue
        $this->assertFalse($this->pauser->isPaused('upgrade:istilldontexist'));
    }

    public function testPauseCallback()
    {
        $this->pauser->pause('upgrade:test8');
        Resque::enqueue('upgrade:test8', 'test');

        $this->assertEquals(0, Resque::redis()->llen('queue:upgrade:test8'));
        $this->assertEquals(1, Resque::redis()->llen('temp:upgrade:test8'));
    }
}
