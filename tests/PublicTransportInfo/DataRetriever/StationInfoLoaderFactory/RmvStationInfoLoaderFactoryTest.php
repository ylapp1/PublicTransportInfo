<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoLoader;
use PublicTransportInfo\DataRetriever\StationInfoLoaderFactory\RmvStationInfoLoaderFactory;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\ConfigParser;

/**
 * Checks that the RmvStationInfoLoaderFactory class works as expected.
 */
class RmvStationInfoLoaderFactoryTest extends TestCase
{
    /**
     * The Cache mock that may be passed to the RmvStationInfoLoaderFactory test instances
     * @var MockObject|Cache $cacheMock
     */
    private $cacheMock;


    /**
     * Method that is called before a test is executed.
     * Initializes the Cache mock.
     */
    protected function setUp()
    {
        $this->cacheMock = $this->getMockBuilder(Cache::class)
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the Cache mock.
     */
    protected function tearDown()
    {
        unset($this->cacheMock);
    }


    /**
     * Checks that an invalid api token config value is detected as expected.
     *
     * @param mixed $_invalidApiTokenValue The invalid api token config value
     *
     * @dataProvider canDetectInvalidApiTokenConfigDataProvider()
     */
    public function testCanDetectInvalidApiTokenConfig($_invalidApiTokenValue)
    {
        $config = new ConfigParser(array("apiToken" => $_invalidApiTokenValue));

        $this->expectExceptionMessage("No api token specified for RmvStationInfoLoaderFactory");
        $factory = new RmvStationInfoLoaderFactory($config, $this->cacheMock);
    }

    /**
     * Returns data sets for the testCanDetectInvalidApiTokenConfig() test.
     * @return array The data sets
     */
    public function canDetectInvalidApiTokenConfigDataProvider(): array
    {
        return array(array(null), array(false), array(""), array(0));
    }


    /**
     * Checks that StationInfoLoader's can be created as expected.
     */
    public function testCanCreateStationInfoLoader()
    {
        $config = new ConfigParser(array("apiToken" => "abc123", "dataFetchInterval" => 240));
        $factory = new RmvStationInfoLoaderFactory($config, $this->cacheMock);

        $stationInfoLoaderA = $factory->createStationInfoLoader(
            "Bahnhofhausen", StationEvent::VEHICLE_TRAIN, array("345")
        );
        $this->assertInstanceOf(StationInfoLoader::class, $stationInfoLoaderA);

        $stationInfoLoaderB = $factory->createStationInfoLoader(
            "Bushaltestellendorf", StationEvent::VEHICLE_BUS, array()
        );
        $this->assertInstanceOf(StationInfoLoader::class, $stationInfoLoaderB);

        $this->assertNotSame($stationInfoLoaderA, $stationInfoLoaderB);
    }
}
