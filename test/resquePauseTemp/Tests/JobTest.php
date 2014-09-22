<?php
/**
 * ResquePause_Job tests.
 *
 * @package		Resque/Tests
 * @author		Wedy Chainy <askwedi@wedipedia.org>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class ResquePause_Tests_JobTest extends PHPUnit_Framework_TestCase
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

	public function testCreate()
	{
		$this->assertTrue((bool)ResquePause_Job::create('upgrade:test'));
		$this->assertTrue((bool)ResquePause_Job::exists('upgrade:test'));
	}

	public function testRemove()
	{
		$this->assertFalse((bool)ResquePause_Job::remove('upgrade:test2'));
		$this->assertFalse((bool)ResquePause_Job::exists('upgrade:test2'));
	}

	public function testRenameToTemp()
	{
		$this->assertFalse((bool)ResquePause_Job::renameToTemp('upgrade:test3'));
	}

	public function testRenameToTempAfterCreated()
	{
		Resque::enqueue('upgrade:test4', 'test');
		$this->assertTrue((bool)ResquePause_Job::renameToTemp('upgrade:test4'));
	}

	public function testRenameBackFromTempAfterCreatedAndSwapped()
	{
		Resque::enqueue('upgrade:test5', 'test');
		ResquePause_Job::renameToTemp('upgrade:test5');
		$this->assertTrue((bool)ResquePause_Job::renameBackFromTemp('upgrade:test5'));
	}
	
	public function testAll()
	{
		$this->assertTrue((bool)ResquePause_Job::create('upgrade:test6'));
		$this->assertTrue((bool)ResquePause_Job::create('upgrade:test7'));
		$this->assertContains('upgrade:test6',ResquePause_Job::all());
		$this->assertContains('upgrade:test7',ResquePause_Job::all());
		$this->assertEquals(count(ResquePause_Job::all()), 2);
	}	

	public function testExists()
	{
		$this->assertTrue((bool)ResquePause_Job::create('upgrade:test8'));
		$this->assertTrue((bool)ResquePause_Job::exists('upgrade:test8'));
	}
}
