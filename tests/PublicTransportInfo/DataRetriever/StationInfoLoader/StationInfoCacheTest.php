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
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoCache;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\Cache\CacheEntry;

/**
 * Checks that the StationInfoCache class works as expected.
 */
class StationInfoCacheTest extends TestCase
{
    /**
     * The Cache mock that will be passed to the StationInfoCache constructor
     * @var MockObject|Cache $cacheMock
     */
    private $cacheMock;

    /**
     * The CacheEntry mock that will be returned by the Cache mock when the StationInfoCache requests an entry
     * @var MockObject|CacheEntry $cacheEntryMock
     */
    private $cacheEntryMock;


    /**
     * Method that is called before a test is started.
     * Initializes the mocks.
     */
    protected function setUp()
    {
        $this->cacheMock = $this->getMockBuilder(Cache::class)
                                ->setMethods(array("getOrCreateEntry"))
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->cacheEntryMock = $this->getMOckBuilder(CacheEntry::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the mocks.
     */
    protected function tearDown()
    {
        unset($this->cacheMock);
        unset($this->cacheEntryMock);
    }


    /**
     * Checks that cached values for the type "departures" are not used when fetching the arrivals.
     */
    public function testCanDetectWrongCachedValuesTypeForArrivals()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/HALLOWELT/last-result.json", 150);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "HALLOWELT", 150);

        $now = new DateTime("now");
        $startTime = new DateTime("2019-05-06 10:30:00");
        $endTime = new DateTime("2019-05-06 12:15:00");

        $this->cacheEntryMock->expects($this->once())
                             ->method("isFor")
                             ->with("arrivals")
                             ->will($this->returnValue(false));
        $this->assertFalse($cache->getArrivals($now, $startTime, $endTime));
    }

    /**
     * Checks that cached values for the type "arrivals" are not used when fetching the departures.
     */
    public function testCanDetectWrongCachedValuesTypeForDepartures()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/universum/last-result.json", 240);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "universum", 240);

        $now = new DateTime("now");
        $startTime = new DateTime("2018-04-02 06:50:00");
        $endTime = new DateTime("2018-04-02 13:41:00");

        $this->cacheEntryMock->expects($this->once())
                             ->method("isFor")
                             ->with("departures")
                             ->will($this->returnValue(false));
        $this->assertFalse($cache->getDepartures($now, $startTime, $endTime));
    }

    /**
     * Checks that cached values are not returned for arrivals if they are not valid (expired or empty).
     */
    public function testCanDetectInvalidCacheEntryForArrivals()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/123456/last-result.json", 410);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "123456", 410);

        $now = new DateTime("now");
        $startTime = new DateTime("2019-03-08 01:20:00");
        $endTime = new DateTime("2019-03-08 22:04:00");

        $this->cacheEntryMock->expects($this->once())
                             ->method("isFor")
                             ->with("arrivals")
                             ->will($this->returnValue(true));
        $this->cacheEntryMock->expects($this->once())
                             ->method("isValid")
                             ->will($this->returnValue(false));
        $this->assertFalse($cache->getArrivals($now, $startTime, $endTime));
    }

    /**
     * Checks that cached values are not returned for departures if they are not valid (expired or empty).
     */
    public function testCanDetectInvalidCacheEntryForDepartures()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/654321/last-result.json", 680);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "654321", 680);

        $now = new DateTime("now");
        $startTime = new DateTime("2019-07-09 00:55:00");
        $endTime = new DateTime("2019-07-09 03:27:00");

        $this->cacheEntryMock->expects($this->once())
                             ->method("isFor")
                             ->with("departures")
                             ->will($this->returnValue(true));
        $this->cacheEntryMock->expects($this->once())
                             ->method("isValid")
                             ->will($this->returnValue(false));
        $this->assertFalse($cache->getDepartures($now, $startTime, $endTime));
    }


    /**
     * Checks that arrivals are returned as expected.
     */
    public function testCanReturnArrivals()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/neu/last-result.json", 40);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "neu", 40);

        $now = new DateTime("now");
        $startTime = new DateTime("2019-02-29 09:35:00");
        $endTime = new DateTime("2019-02-29 13:00:00");

        $mockedArrivals = array(
            (object)array("time" => "10:35:00"),
            (object)array("time" => "11:15:00")
        );
        $this->initializeValidCacheEntryExpectations("arrivals", $mockedArrivals);

        $arrivals = $cache->getArrivals($now, $startTime, $endTime);
        $this->assertEquals($mockedArrivals, $arrivals);
    }

    /**
     * Checks that departures are returned as expected.
     */
    public function testCanReturnDepartures()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/B123W3298W/last-result.json", 600);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "B123W3298W", 600);

        $now = new DateTime("now");
        $startTime = new DateTime("2019-04-01 08:14:00");
        $endTime = new DateTime("2019-04-01 14:11:00");

        $mockedDepartures = array(
            (object)array("time" => "10:55:00"),
            (object)array("time" => "12:10:00")
        );
        $this->initializeValidCacheEntryExpectations("departures", $mockedDepartures);

        $departures = $cache->getDepartures($now, $startTime, $endTime);
        $this->assertEquals($mockedDepartures, $departures);
    }

    /**
     * Checks that arrivals outside the target time span are removed from the returned results.
     */
    public function testCanRemoveArrivalsOutsideTargetTimeSpan()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/zuviele/last-result.json", 4000);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "zuviele", 4000);

        $now = new DateTime("now");
        $startTime = new DateTime("2016-12-31 07:34:00");
        $endTime = new DateTime("2016-12-31 14:01:00");

        $mockedArrivals = array(
            (object)array("time" => "07:29:00"),
            (object)array("time" => "11:15:00"),
            (object)array("time" => "11:30:00"),
            (object)array("time" => "14:06:00"),
            (object)array("time" => "14:01:00"),
            (object)array("time" => "06:09:00"),
            (object)array("time" => "07:34:00"),
            (object)array("time" => "07:33:59"),
            (object)array("time" => "14:01:01")
        );
        $expectedArrivals = array(
            (object)array("time" => "11:15:00"),
            (object)array("time" => "11:30:00"),
            (object)array("time" => "14:01:00"),
            (object)array("time" => "07:34:00"),
        );

        $this->initializeValidCacheEntryExpectations("arrivals", $mockedArrivals);
        $this->initializeCacheEntryUpdateExpectations($expectedArrivals);

        $arrivals = $cache->getArrivals($now, $startTime, $endTime);
        $this->assertEquals($expectedArrivals, $arrivals);
    }

    /**
     * Checks that departures outside the target time span are removed from the returned results.
     */
    public function testCanRemoveDeparturesOutsideTargetTimeSpan()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/fastfertig/last-result.json", 2003);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "fastfertig", 2003);

        $now = new DateTime("now");
        $startTime = new DateTime("2015-01-27 05:44:00");
        $endTime = new DateTime("2015-01-27 16:12:00");

        $mockedArrivals = array(
            (object)array("time" => "04:27:00"),
            (object)array("time" => "05:43:59"),
            (object)array("time" => "14:56:00"),
            (object)array("time" => "16:12:01"),
            (object)array("time" => "15:03:00"),
            (object)array("time" => "00:08:00"),
            (object)array("time" => "18:46:00"),
            (object)array("time" => "16:12:00"),
            (object)array("time" => "05:44:00")
        );
        $expectedArrivals = array(
            (object)array("time" => "14:56:00"),
            (object)array("time" => "15:03:00"),
            (object)array("time" => "16:12:00"),
            (object)array("time" => "05:44:00")
        );

        $this->initializeValidCacheEntryExpectations("departures", $mockedArrivals);
        $this->initializeCacheEntryUpdateExpectations($expectedArrivals);

        $arrivals = $cache->getDepartures($now, $startTime, $endTime);
        $this->assertEquals($expectedArrivals, $arrivals);
    }


    /**
     * Checks that arrivals can be set as expected.
     */
    public function testCanSetArrivals()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/setzen6/last-result.json", 1234);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "setzen6", 1234);
        $now = new DateTime("now");

        $this->initializeCacheEntrySetDataExpectations(array("some values"), "arrivals", $now);
        $cache->setArrivals($now, array("some values"));
    }

    /**
     * Checks that departures can be set as expected.
     */
    public function testCanSetDepartures()
    {
        $this->initializeCacheExpectations("RmvStationInfoLoader/23final/last-result.json", 9999);
        $cache = new StationInfoCache($this->cacheMock, "RmvStationInfoLoader", "23final", 9999);
        $now = new DateTime("now");

        $this->initializeCacheEntrySetDataExpectations(array("some other values", "very creative", INF), "departures", $now);
        $cache->setDepartures($now, array("some other values", "very creative", INF));
    }


    /**
     * Initializes the expectations for the Cache mock.
     * This mocks the "getOrCreateEntry" method and returns the CacheEntry mock.
     *
     * @param string $_cacheEntryName The expected cache entry name
     * @param int $_validForSeconds The expected number of valid for seconds
     */
    private function initializeCacheExpectations(string $_cacheEntryName, int $_validForSeconds)
    {
        $this->cacheMock->expects($this->once())
                        ->method("getOrCreateEntry")
                        ->with($_cacheEntryName, $_validForSeconds)
                        ->will($this->returnValue($this->cacheEntryMock));
    }

    /**
     * Initializes the expectations for valid CacheEntry's.
     * This returns true on the "isFor" call and also returns true on the "isValid" call.
     * Finally it returns some data on the "getData" call.
     *
     * @param string $_type The expected station infos type ("arrivals" or "departures")
     * @param mixed $_data The data that the "getData" call will return
     */
    private function initializeValidCacheEntryExpectations(string $_type, $_data)
    {
        $this->cacheEntryMock->expects($this->once())
                             ->method("isFor")
                             ->with($_type)
                             ->will($this->returnValue(true));

        $this->cacheEntryMock->expects($this->once())
                             ->method("isValid")
                             ->will($this->returnValue(true));

        $this->cacheEntryMock->expects($this->once())
                             ->method("getData")
                             ->will($this->returnValue($_data));
    }

    /**
     * Initializes the expectations for when a CacheEntry is updated.
     * This expects a "setData" call with some data and a following "save" call.
     *
     * @param object[] $_updatedData The expected updated data
     */
    private function initializeCacheEntryUpdateExpectations(array $_updatedData)
    {
        $this->cacheEntryMock->expects($this->once())
                             ->method("setData")
                             ->with($_updatedData)
                             ->willReturn($this->cacheEntryMock);

        $this->cacheEntryMock->expects($this->once())
                             ->method("save");
    }

    /**
     * Initializes the expectations for when the arrivals or departures are set manually.
     *
     * @param mixed $_data The expected data to which the arrivals or departures are set
     * @param string $_type The expected station infos type ("arrivals" or "departures")
     * @param DateTime $_timestamp The expected timestamp
     */
    private function initializeCacheEntrySetDataExpectations($_data, $_type, $_timestamp)
    {
        $this->cacheEntryMock->expects($this->once())
                             ->method("setData")
                             ->with($_data)
                             ->will($this->returnValue($this->cacheEntryMock));

        $this->cacheEntryMock->expects($this->once())
                             ->method("setFor")
                             ->with($_type)
                             ->will($this->returnValue($this->cacheEntryMock));

        $this->cacheEntryMock->expects($this->once())
                             ->method("setCreateTimestamp")
                             ->with($_timestamp)
                             ->will($this->returnValue($this->cacheEntryMock));

        $this->cacheEntryMock->expects($this->once())
                             ->method("save");
    }
}
