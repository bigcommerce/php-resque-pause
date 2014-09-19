<?php
/**
 * Resque Pause job.
 *
 * @package		ResquePause/Job
 * @author		Wedy Chainy <askwedi@wedipedia.org>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class ResquePause_Job
{
    /**
     * @var string default namespace for pause
     */
    private static $defaultNamespace = 'pause:';

    /*
     * @var string default namespace for temporary pause
     */
    private static $defaultTempNamespace = 'temp:';


    /*
     * @var string default original queue prefix
     */
    private static $defaultOriginalQueueNamespace = 'queue:';

    /**
     * @var string default set for pause
     */
    private static $defaultSet = 'pauses';

    /**
     * Create a new pause job
     *
     * @param string $queue The name of the queue to place the job in.
     */
    public static function create($queue)
    {
	Resque::redis()->sadd(self::$defaultSet, $queue);
	return Resque::redis()->set(self::$defaultNamespace . $queue, true);
    }

    /**
     * Delete a pause job
     *
     * @param string $queue The name of the queue to place the job in.
     */
    public static function remove($queue)
    {
	Resque::redis()->del(self::$defaultNamespace . $queue);
	return Resque::redis()->srem(self::$defaultSet, $queue);
    }

    public static function renameToTemp($queue, $queuePrefix = self::$defaultOriginalQueueNamespace)
    {
	return Resque::redis()->rename($queuePrefix . $queue, self::$defaultTempNamespace . $queue);
    }

    public static function renameBackFromTemp($queue, $queuePrefix = self::$defaultOriginalQueueNamespace)
    {
	return Resque::redis()->rename(self::$defaultTempNamespace . $queue, $queuePrefix . $queue);
    }

    /**
    * Return all pause jobs known to Resque as instantiated instances.
    * @return array
    */
    public static function all()
    {
	return Resque::redis()->smembers(self::$defaultSet);
    }

    /**
     * Simply Is this Job paused?
     *
     * @param string $queue The name of the queue to place the job in.
     *
     * @return bool/integer
     */
    public static function exists($queue)
    {
	return (bool)Resque::redis()->sismember(self::$defaultSet, $queue);
    }

}
?>
