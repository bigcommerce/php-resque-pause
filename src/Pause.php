<?php
namespace Resque\Plugins;

use Resque;
use Resque_Event;
use Resque_Redis;
use Resque_Job_DontCreate;

/**
 * Class Pause Pauses Resque queues
 * @package Resque\Plugins
 */
class Pause
{
    /** @var JobPauser  */
    private $pauser = null;

    /** @var JobPauser  */
    private $listener = null;

    public function __construct()
    {
        // Create object for pausing jobs
        $this->pauser = new JobPauser(Resque::redis(), Resque_Redis::getPrefix());
        $pauser = $this->pauser;

        // Listen for enqueue and move any new jobs to temp queue
        $this->listener = function ($class, $args, $queue, $id) use ($pauser) {
            if ($pauser->isPaused($queue)) {
                $args = !is_null($args) ? $args : array();
                if (!is_array($args)) {
                    throw new \InvalidArgumentException('Supplied $args must be an array.');
                }

                $pauser->pushPausedJob($queue, $class, $args, $id);
                // Stop the original job from being created
                throw new Resque_Job_DontCreate;
            }
        };
        Resque_Event::listen('beforeEnqueue', $this->listener);
    }

    public function __destruct()
    {
        Resque_Event::stopListening('beforeEnqueue', $this->listener);

    }

    /**
     * Pause the job — create a flag and rename the original queue to temporary queue
     *
     * @param string $queue The name of the queue to fetch an item from.
     * @return boolean
     */
    public function pause($queue)
    {
        return $this->pauser->pause($queue) && $this->pauser->renameToTemp($queue);
    }

    /**
     * Resume the job — remove flag and rename the temporary queue back to the original one
     *
     * @param string $queue The name of the queue to fetch an item from.
     * @return boolean
     */
    public function resume($queue)
    {
        return $this->pauser->resume($queue) && $this->pauser->renameBackFromTemp($queue);
    }

    /**
     * Check if the queue is paused
     *
     * @param string $queue The name of the queue to be used
     * @return bool
     */
    public function isPaused($queue)
    {
        return $this->pauser->isPaused($queue);
    }
}
