<?php
namespace Unit\Resque\Plugins\Tests;

use Resque\Plugins\JobPauser;
use PHPUnit_Framework_TestCase;

/**
 * Job tests.
 *
 * @package     Resque/Tests
 * @author      Wedy Chainy <wedy.chainy@bigcommerce.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class JobPauserTest extends PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $redisMock = null;

    public function setUp()
    {
        $this->redisMock = $this->getMockBuilder('Resque_Redis')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'sadd',
                'srem',
                'getPrefix',
                'rename',
                'sismember',
            ))
            ->getMock();
    }

    public function createDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider createDataProvider
     * @param bool $saddSuccess
     */
    public function testCreate($saddSuccess, $returnSuccess)
    {
        $this->redisMock
            ->expects($this->once())
            ->method('sadd')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test'))
            ->willReturn($saddSuccess);

        $pauser = new JobPauser($this->redisMock, 'resqueFaker:');
        $this->assertEquals($returnSuccess, $pauser->pause('upgrade:test'));
    }

    public function removeDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider removeDataProvider
     * @param bool $sremSuccess
     */
    public function testRemove($sremSuccess, $returnSuccess)
    {
        $this->redisMock
            ->expects($this->once())
            ->method('srem')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test2'))
            ->willReturn($sremSuccess);

        $pauser = new JobPauser($this->redisMock, 'resqueFaker:');
        $this->assertEquals($returnSuccess, $pauser->resume('upgrade:test2'));
    }

    public function renameDataprovider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider renameDataProvider
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameToTemp($renameSuccess, $expectedResult)
    {
        $this->redisMock
            ->expects($this->once())
            ->method('rename')
            ->with($this->equalTo('queue:upgrade:test3'), $this->equalTo('resqueFaker:temp:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->redisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameToTemp('upgrade:test3'));
    }

    /**
     * @dataProvider renameDataProvider Reuses the renaming provider because the cases are the same
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameBackFromTemp($renameSuccess, $expectedResult)
    {
        $this->redisMock
            ->expects($this->once())
            ->method('rename')
            ->with($this->equalTo('temp:upgrade:test3'), $this->equalTo('resqueFaker:queue:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->redisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameBackFromTemp('upgrade:test3'));
    }

    public function existsDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider existsDataProvider
     * @param bool $existsSuccess
     * @param bool $expectedResult
     */
    public function testExists($existsSuccess, $expectedResult)
    {
        $this->redisMock
            ->expects($this->once())
            ->method('sismember')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test8'))
            ->willReturn($existsSuccess);

        $pauser = new JobPauser($this->redisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->isPaused('upgrade:test8'));
    }
}
