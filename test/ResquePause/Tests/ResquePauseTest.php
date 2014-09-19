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
		#TODO
	}

	public function testUnpause()
	{
		#TODO
	}

	public function testIsPaused()
	{
		#TODO
	}

	public function testPauseCallback()
	{
		#TODO
	}


}
