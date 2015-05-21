<?php
namespace Resque\Plugins;

/**
 * Resque Pause job.
 *
 * @author      Wedy Chainy <wedy.chainy@bigcommerce.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class JobPauser
{
    /** @var string default namespace for temporary pause */
    private static $tempQueuePrefix = 'temp:';

    /** @var string default set for pause */
    private static $pausedSetName = 'pauses';

    /** @var \Resque_Redis */
    private $redis = null;

    /** @var string */
    private $resqueRedisPrefix = null;

    public function __construct($redis, $resqueRedisPrefix)
    {
        $this->redis = $redis;
        $this->resqueRedisPrefix = $resqueRedisPrefix;
    }

    /**
     * Mark a queue as paused
     *
     * @param string $queue The name of the queue to mark as paused.
     * @return bool
     */
    public function pause($queue)
    {
        return $this->isPaused($queue) || $this->redis->sadd(self::$pausedSetName, $queue);
    }

    /**
     * Remove the `paused` marker from a queue
     *
     * @param string $queue The name of the queue to unmark.
     * @return bool
     */
    public function resume($queue)
    {
        return !$this->isPaused($queue) || $this->redis->srem(self::$pausedSetName, $queue);
    }

    /**
     * Rename original queue to temp queue
     *
     * @param string $queue
     * @param string $queuePrefix original queue's prefix , by default if you use PHP-resque it's 'queue'
     * @return bool
     */
    public function renameToTemp($queue, $queuePrefix = 'queue:')
    {
        if ($this->queueIsEmpty($queuePrefix . $queue)) {
            return true;
        }
        return $this->redis->rename(
            $queuePrefix . $queue,
            $this->resqueRedisPrefix . self::$tempQueuePrefix . $queue
        );
    }

    /**
     * Rename back from temp to original
     *
     * @param string $queue
     * @param string $queuePrefix original queue's prefix , by default if you use PHP-resque it's 'queue'
     * @return bool
     */
    public function renameBackFromTemp($queue, $queuePrefix = 'queue:')
    {
        if ($this->queueIsEmpty(self::$tempQueuePrefix . $queue)) {
            return true;
        }
        return $this->redis->rename(
            self::$tempQueuePrefix . $queue,
            $this->resqueRedisPrefix . $queuePrefix . $queue
        );
    }

    /**
     * Push a job to the paused queue
     *
     * @param string $queue
     * @param string $class
     * @param array $args
     * @param string $id
     */
    public function pushPausedJob($queue, $class, array $args, $id)
    {
        $this->redis->rpush("temp:$queue", json_encode(array(
            'class' => $class,
            'args'  => $args,
            'id'    => $id,
            'queue_time' => microtime(true)
        )));
    }

    /**
     * @param string $queue The name of the queue to check.
     * @return bool
     */
    public function isPaused($queue)
    {
        return (bool)$this->redis->sismember(self::$pausedSetName, $queue);
    }

    /**
     * Check if a queue has anything in it which is the same as an existence check
     *
     * @param $queue
     * @return bool
     */
    public function queueIsEmpty($queue)
    {
        return $this->redis->llen($queue) === 0;
    }
}
