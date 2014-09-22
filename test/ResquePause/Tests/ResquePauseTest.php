<?php
/**
 * ResquePause_Job tests.
 *
 * @package		Resque/Tests
 * @author		Wedy Chainy <askwedi@wedipedia.org>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class ResquePause_Tests_ResquePauseTest extends PHPUnit_Framework_TestCase
{
	protected $resque;
	protected $redis;
	
	public function setUp()
	{
		$config = file_get_contents(REDIS_CONF);
		preg_match('#^\s*port\s+([0-9]+)#m', $config, $matches);
		$this->redis = new Credis_Client('localhost', $matches[1]);
		$this->redis->flushAll();

	}

	public function testPause()
	{
		Resque::enqueue('upgrade:test', 'test');
		Resque::enqueue('upgrade:test', 'test');
		$this->assertEquals(Resque::size('upgrade:test'), 2);
		$this->assertTrue((bool)ResquePause::pause('upgrade:test'));
		$this->assertEquals(Resque::size('upgrade:test'), 0);
	}
	
	public function testPauseNonExistQueue()
	{
		$this->assertFalse((bool)ResquePause::pause('upgrade:test2'));
	}

	public function testUnpause()
	{
		Resque::enqueue('upgrade:test3', 'test');
		Resque::enqueue('upgrade:test3', 'test');
		ResquePause::pause('upgrade:test3');
		$this->assertTrue((bool)ResquePause::unpause('upgrade:test3'));
		$this->assertEquals(Resque::size('upgrade:test3'), 2);
	}

	public function testUnpauseButNeverBeenPausedBefore()
	{
		Resque::enqueue('upgrade:test4', 'test');
		Resque::enqueue('upgrade:test4', 'test');
		$this->assertFalse((bool)ResquePause::unpause('upgrade:test4'));
	}

	public function testIsPaused()
	{
		Resque::enqueue('upgrade:test5', 'test');
		ResquePause::pause('upgrade:test5');
		$this->assertTrue((bool)ResquePause::isPaused('upgrade:test5'));
	}
	
	public function testIsPaused2()
	{
		Resque::enqueue('upgrade:test6', 'test');
		$this->assertFalse((bool)ResquePause::isPaused('upgrade:test6'));
	}

	public function testIsPaused3()
	{
		Resque::enqueue('upgrade:test7', 'test');
		ResquePause::pause('upgrade:test7');
		ResquePause::unpause('upgrade:test7');
		$this->assertFalse((bool)ResquePause::isPaused('upgrade:test7'));
	}

	public function testPauseCallback()
	{
		# we have 2 in queue:upgrade:test8
		Resque::enqueue('upgrade:test8', 'test');
		Resque::enqueue('upgrade:test8', 'test');

		# pause it!
		ResquePause::pause('upgrade:test8');

		try {
		#  before enqueue pauseCallback
		ResquePause::pauseCallback('test', array(), 'upgrade:test8', 'dummyid');
		}
	  catch(Resque_Job_DontCreate $e)	{}
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test8'), 0);
		$this->assertEquals(Resque::redis()->llen('temp:upgrade:test8'), 3);
	}
	public function testbeforeEnqueuePauseCallback()
	{
		ResquePause::beforeEnqueuePauseCallback();

		#UNPAUSE
		Resque::enqueue('upgrade:test3', 'test');
		Resque::enqueue('upgrade:test3', 'test');
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test3'), 2);

		#PAUSE IT
		ResquePause::pause('upgrade:test3');
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test3'), 0);
		$this->assertEquals(Resque::redis()->llen('temp:upgrade:test3'), 2);

		#QUEUE IT while PAUSED
		Resque::enqueue('upgrade:test3', 'test');
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test3'), 0);
		$this->assertEquals(Resque::redis()->llen('temp:upgrade:test3'), 3);

		#UNPAUSE
		ResquePause::unpause('upgrade:test3');
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test3'), 3);
		$this->assertEquals(Resque::redis()->llen('temp:upgrade:test3'), 0);

		#QUEUE it again
		Resque::enqueue('upgrade:test3', 'test');
		$this->assertEquals(Resque::redis()->llen('queue:upgrade:test3'), 4);
		$this->assertEquals(Resque::redis()->llen('temp:upgrade:test3'), 0);

	}
}
