<?php
/**
 * Base Resque Pause class
 *
 * @package		Resque Pause
 * @author		Wedy Chainy <askwedi@wedipedia.org>
 * @licence		http://www.opensource.org/licenses/mit-license.php
 */

class ResquePause
{
    const VERSION = '0.1';

    /**
     * Pause the job
     *
     * create a flag, and rename the original queue to temporary queue
     *
     * @param string $queue The name of the queue to fetch an item from.
     * 
     * @return boolean
     */ 
    public static function pause($queue)
    {
	$result = ResquePause_Job::create($queue);
	if($result) {
	    return ResquePause_Job::renameToTemp($queue);
	}
	return false
    }

    /**
     * Unpause the job
     *
     * remove flag, and rename the temporary queue back to the original one
     *
     * @param string $queue The name of the queue to fetch an item from.
     * 
     * @return boolean
     */ 
    public static function unpause($queue)
    {
	$result = ResquePause_Job::remove($queue);
	if($result) {
	    return ResquePause_Job::renameBackFromTemp($queue);
	}
	return false;
    }

    /**
     * is paused? 
     *
     * @param string $queue The name of the queue to fetch an item from.
     * 
     * @return boolean
     */ 
    public static function isPaused($queue)
    {
	return ResquePause_Job::exists($queue);
    }

    /**
     * beforeEnqueue Callback
     */
    public static function beforeEnqueuePauseCallback()
    {
	Resque_Event::Listen('beforeEnqueue', "ResquePause::pauseCallback");
    }

    /*
     * pause callback
     */
    public static function pauseCallback($hookParams)
    {
	$class = $hookParams['class'];
	$args  = $hookParams['args'];
	$queue = $hookParams['queue'];
	$id    = $hookParams['id'];

	if(self::isPaused($queue)) 
	{
	    if($args !== null && !is_array($args)) {
		throw new InvalidArgumentException('Supplied $args must be an array.');
	    }
	    
	    $id = md5(uniqid('', true));
	    $item = array('class' => $class,
			  'args'  => array($args),
			  'id'    => $id,
			  'queue_time' => microtime(true));
	    self::redis()->rpush('queue:' . $queue, json_encode($item));
	    throw new Resque_Job_DontCreate;
	}
    }
}
