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
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoCache;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\StationInfoFetcher;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoLoader;

/**
 * Base class for tests that test child classes of the StationInfoLoader class.
 */
class StationInfoLoaderTest extends TestCase
{
    /**
     * The StationInfoCache mock of the test class instances
     * @var MockObject|StationInfoCache $stationInfoCacheMock
     */
    private $stationInfoCacheMock;

    /**
     * The StationInfoFetcher mock of the test class instances
     * @var MockObject|StationInfoFetcher $infoFetcherMock
     */
    private $infoFetcherMock;


    /**
     * Method that is called before a test is executed.
     * Initializes the mocks.
     */
    protected function setUp()
    {
        $this->stationInfoCacheMock = $this->getMockBuilder(StationInfoCache::class)
                                           ->setMethods(array(
                                               "getArrivals", "getDepartures", "setArrivals", "setDepartures"
                                           ))
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->infoFetcherMock = $this->getMockBuilder(StationInfoFetcher::class)
                                      ->setMethods(array("fetchArrivals", "fetchDepartures"))
                                      ->disableOriginalConstructor()
                                      ->getMock();
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the mocks.
     */
    protected function tearDown()
    {
        unset($this->stationInfoCacheMock);
        unset($this->infoFetcherMock);
    }


    /**
     * Checks that an empty result is returned if the start time is bigger than the end time.
     */
    public function testReturnsEmptyResultIfStartTimeIsBiggerThanEndTime()
    {
        $loader = $this->getTestInstance();
        $now = new DateTime("now");
        $startDate = new DateTime("2019-10-03 10:00:00");
        $endDate = new DateTime("2019-10-03 08:00:00");

        $this->assertCount(0, $loader->loadArrivals($now, $startDate, $endDate));
        $this->assertCount(0, $loader->loadDepartures($now, $startDate, $endDate));
    }

    /**
     * Checks that the contents of the StationInfoCache are returned if available.
     */
    public function testCanUseCachedResults()
    {
        $loader = $this->getTestInstance();
        $now = new DateTime("now");
        $startDate = new DateTime("2019-10-03 10:00:00");
        $endDate = new DateTime("2019-10-03 12:00:00");

        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("getArrivals")
                                   ->with($now, $startDate, $endDate)
                                   ->will($this->returnValue(array("#cached_arrivals#")));
        $arrivals = $loader->loadArrivals($now, $startDate, $endDate);
        $this->assertEquals(array("#cached_arrivals#"), $arrivals);

        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("getDepartures")
                                   ->with($now, $startDate, $endDate)
                                   ->will($this->returnValue(array("#cached_departures#")));
        $departures = $loader->loadDepartures($now, $startDate, $endDate);
        $this->assertEquals(array("#cached_departures#"), $departures);
    }

    /**
     * Checks that arrivals are loaded as expected.
     */
    public function testCanLoadArrivals()
    {
        $loader = $this->getTestInstance();
        $now = new DateTime("now");
        $startDate = new DateTime("2019-10-03 15:00:00");
        $endDate = new DateTime("2019-10-03 17:00:00");

        // Should attempt to load arrivals from cache
        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("getArrivals")
                                   ->with($now, $startDate, $endDate)
                                   ->will($this->returnValue(false));

        // Should load arrivals via the info fetcher
        $arrivalMock = $this->getEventMock("#json-arrival#");
        $this->infoFetcherMock->expects($this->once())
                              ->method("fetchArrivals")
                              ->with($startDate, $endDate)
                              ->will($this->returnValue(array($arrivalMock)));

        // Should cache the loaded arrivals
        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("setArrivals")
                                   ->with($now, array("#json-arrival#"));


        $arrivals = $loader->loadArrivals($now, $startDate, $endDate);
        $this->assertEquals(array("#json-arrival#"), $arrivals);
    }

    /**
     * Checks that departures are loaded as expected.
     */
    public function testCanLoadDepartures()
    {
        $loader = $this->getTestInstance();
        $now = new DateTime("now");
        $startDate = new DateTime("2019-10-03 19:00:00");
        $endDate = new DateTime("2019-10-03 19:30:00");

        // Should attempt to load departures from cache
        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("getDepartures")
                                   ->with($now, $startDate, $endDate)
                                   ->will($this->returnValue(false));

        // Should load arrivals via the info fetcher
        $departureMock = $this->getEventMock("#json-departure#");
        $this->infoFetcherMock->expects($this->once())
                              ->method("fetchDepartures")
                              ->with($startDate, $endDate)
                              ->will($this->returnValue(array($departureMock)));

        // Should cache the loaded departures
        $this->stationInfoCacheMock->expects($this->once())
                                   ->method("setDepartures")
                                   ->with($now, array("#json-departure#"));


        $departures = $loader->loadDepartures($now, $startDate, $endDate);
        $this->assertEquals(array("#json-departure#"), $departures);
    }


    /**
     * Returns a StationInfoLoader instance.
     *
     * @return StationInfoLoader The test class instance
     */
    private function getTestInstance(): StationInfoLoader
    {
        return new StationInfoLoader($this->stationInfoCacheMock, $this->infoFetcherMock);
    }

    /**
     * Returns a StationEvent mock that expects to be converted to a json object.
     *
     * @param mixed $_mockedJson The value to return as json object representation
     *
     * @return MockObject The StationEvent mock
     */
    private function getEventMock($_mockedJson): MockObject
    {
        $arrivalMock = $this->getMockBuilder(StationEvent::class)
                            ->setMethods(array("toJsonObject"))
                            ->disableOriginalConstructor()
                            ->getMock();
        $arrivalMock->expects($this->once())
                    ->method("toJsonObject")
                    ->will($this->returnValue($_mockedJson));

        return $arrivalMock;
    }
}
