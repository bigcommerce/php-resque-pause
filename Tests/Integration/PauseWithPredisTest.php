<?php
namespace Resque\Plugins\Tests\Integration;

use Resque;
use Predis\Client;

/**
 * Pause tests.
 *
 * @package     PHP Resque Pause
 * @author      Wedy Chainy <wedy.chainy@bigcommerce.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class PauseWithPredisTest extends PauseTestBase
{
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

        $clientParams = array(
            'host' => 'localhost',
            'port' => $matches[1],
            'prefix' => 'resque:',
        );
        Resque::setBackend(function () use ($clientParams) {
            return new Client($clientParams);
        });
    }
}
