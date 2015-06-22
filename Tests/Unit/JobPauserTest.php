<?php
namespace Resque\Plugins\Tests\Unit;

use Resque\Plugins\JobPauser;
use PHPUnit_Framework_TestCase;

/**
 * Class JobPauserTest
 * @package Resque\Plugins\Tests\Unit
 */
class JobPauserTest extends PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $resqueRedisMock = null;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $predisMock = null;

    public function setUp()
    {
        $this->resqueRedisMock = $this->getMockBuilder('Resque_Redis')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'sadd',
                'srem',
                'getPrefix',
                'llen',
                'rename',
                'sismember',
            ))
            ->getMock();

        $this->predisMock = $this->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'sadd',
                'srem',
                'getPrefix',
                'llen',
                'rename',
                'sismember',
            ))
            ->getMock();
    }

    public function pauseDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider pauseDataProvider
     * @param bool $saddSuccess
     * @param bool $returnSuccess
     */
    public function testPause($saddSuccess, $returnSuccess)
    {
        $this->resqueRedisMock
            ->expects($this->once())
            ->method('sadd')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test'))
            ->willReturn($saddSuccess);

        $pauser = new JobPauser($this->resqueRedisMock, 'resqueFaker:');
        $this->assertEquals($returnSuccess, $pauser->pause('upgrade:test'));
    }

    public function resumeDataProvider()
    {
        return array(
            array(true, true, true),
            array(true, false, false),
            array(false, true, true),
            array(false, false, true),
        );
    }

    /**
     * @dataProvider resumeDataProvider
     * @param bool $isPaused
     * @param bool $sremSuccess
     * @param bool $returnSuccess
     */
    public function testResume($isPaused, $sremSuccess, $returnSuccess)
    {
        $this->resqueRedisMock
            ->expects($this->once())
            ->method('sismember')
            ->with($this->equalTo('pauses'))
            ->willReturn($isPaused);

        $this->resqueRedisMock
            ->expects($isPaused ? $this->once() : $this->never())
            ->method('srem')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test2'))
            ->willReturn($sremSuccess);

        $pauser = new JobPauser($this->resqueRedisMock, 'resqueFaker:');
        $this->assertEquals($returnSuccess, $pauser->resume('upgrade:test2'));
    }

    public function renameDataprovider()
    {
        return array(
            array(1, true, true),
            array(1, false, false),
            array(0, true, true),
            array(0, false, true),
        );
    }

    /**
     * @dataProvider renameDataProvider
     * @param int $queueLength
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameToTemp($queueLength, $renameSuccess, $expectedResult)
    {
        $this->resqueRedisMock
            ->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('queue:upgrade:test3'))
            ->willReturn($queueLength);

        $this->resqueRedisMock
            ->expects($queueLength ? $this->once() : $this->never())
            ->method('rename')
            ->with($this->equalTo('queue:upgrade:test3'), $this->equalTo('resqueFaker:temp:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->resqueRedisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameToTemp('upgrade:test3'));
    }


    /**
     * @dataProvider renameDataProvider
     * @param int $queueLength
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameToTempWithPredis($queueLength, $renameSuccess, $expectedResult)
    {
        $this->predisMock
            ->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('queue:upgrade:test3'))
            ->willReturn($queueLength);

        $this->predisMock
            ->expects($queueLength ? $this->once() : $this->never())
            ->method('rename')
            ->with($this->equalTo('queue:upgrade:test3'), $this->equalTo('temp:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->predisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameToTemp('upgrade:test3'));
    }

    /**
     * @dataProvider renameDataProvider Reuses the renaming provider because the cases are the same
     * @param int $queueLength
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameBackFromTemp($queueLength, $renameSuccess, $expectedResult)
    {
        $this->resqueRedisMock
            ->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('temp:upgrade:test3'))
            ->willReturn($queueLength);

        $this->resqueRedisMock
            ->expects($queueLength ? $this->once() : $this->never())
            ->method('rename')
            ->with($this->equalTo('temp:upgrade:test3'), $this->equalTo('resqueFaker:queue:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->resqueRedisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameBackFromTemp('upgrade:test3'));
    }

    /**
     * @dataProvider renameDataProvider Reuses the renaming provider because the cases are the same
     * @param int $queueLength
     * @param bool $renameSuccess
     * @param bool $expectedResult
     */
    public function testRenameBackFromTempWithPredis($queueLength, $renameSuccess, $expectedResult)
    {
        $this->predisMock
            ->expects($this->once())
            ->method('llen')
            ->with($this->equalTo('temp:upgrade:test3'))
            ->willReturn($queueLength);

        $this->predisMock
            ->expects($queueLength ? $this->once() : $this->never())
            ->method('rename')
            ->with($this->equalTo('temp:upgrade:test3'), $this->equalTo('queue:upgrade:test3'))
            ->willReturn($renameSuccess);

        $pauser = new JobPauser($this->predisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->renameBackFromTemp('upgrade:test3'));
    }

    public function isPausedDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider isPausedDataProvider
     * @param bool $existsSuccess
     * @param bool $expectedResult
     */
    public function testIsPaused($existsSuccess, $expectedResult)
    {
        $this->resqueRedisMock
            ->expects($this->once())
            ->method('sismember')
            ->with($this->equalTo('pauses'), $this->equalTo('upgrade:test8'))
            ->willReturn($existsSuccess);

        $pauser = new JobPauser($this->resqueRedisMock, 'resqueFaker:');
        $this->assertEquals($expectedResult, $pauser->isPaused('upgrade:test8'));
    }
}
