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
use PublicTransportInfo\Api\Rmv\Request\ArrivalBoardRequest;
use PublicTransportInfo\Api\Rmv\Request\DepartureBoardRequest;
use PublicTransportInfo\Api\Rmv\Request\StationBoardRequest;
use PublicTransportInfo\Api\RmvApi;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser\RmvInfoParser;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\RmvStationInfoFetcher;

/**
 * Checks that the RmvStationInfoFetcher class works as expected.
 */
class RmvStationInfoFetcherTest extends TestCase
{
    /**
     * The RmvApi mock for the RmvStationInfoFetcher test instances
     *
     * @var MockObject|RmvApi $rmvApiMock
     */
    private $rmvApiMock;

    /**
     * The RmvInfoParser mock for the RmvStationInfoFetcher test instance
     *
     * @var MockObject|RmvInfoParser $infoParserMock
     */
    private $infoParserMock;


    /**
     * Method that is called before a test is executed.
     * Initializes the mocks.
     */
    protected function setUp()
    {
        $this->rmvApiMock = $this->getMockBuilder(RmvApi::class)
                                 ->setMethods(array("doRequest"))
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->infoParserMock = $this->getMockBuilder(RmvInfoParser::class)
                                     ->setMethods(array("parseArrivalInfos", "parseDepartureInfos"))
                                     ->getMock();
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the mocks.
     */
    protected function tearDown()
    {
        unset($this->rmvApiMock);
        unset($this->infoParserMock);
    }


    /**
     * Checks whether arrivals can be fetched as expected.
     */
    public function testCanFetchArrivals()
    {
        $infoFetcher = $this->getTestInstance("FAKESTATION", array("R48"), StationEvent::VEHICLE_TRAIN);

        $startTime = new DateTime("2019-10-04 10:40:00");
        $endTime = new DateTime("2019-10-04 12:10:00");

        // An API request should be triggered
        $this->rmvApiMock->expects($this->once())
                         ->method("doRequest")
                         ->with($this->callback(function(ArrivalBoardRequest $_request){
                             $this->assertInstanceOf(ArrivalBoardRequest::class, $_request);

                             $this->assertEquals(
                                 array(
                                     "extId" => "FAKESTATION",
                                     "rtMode" => StationBoardRequest::RT_MODE_REALTIME,
                                     "date" => "2019-10-04",
                                     "time" => "10:40",
                                     "duration" => "90",
                                     "lines" => "!R48"
                                 ),
                                 $_request->getRequestParameters()
                             );

                             return true;
                         }))
                         ->will($this->returnValue(array("hallo" => "test", "wert" => 5)));

        // The response of the API request should be parsed
        $this->infoParserMock->expects($this->once())
                             ->method("parseArrivalInfos")
                             ->with(array("hallo" => "test", "wert" => 5), StationEvent::VEHICLE_TRAIN)
                             ->will($this->returnValue(array("#parsed-arrival-infos#")));

        $arrivals = $infoFetcher->fetchArrivals($startTime, $endTime);
        $this->assertEquals(array("#parsed-arrival-infos#"), $arrivals);
    }

    /**
     * Checks whether departures can be fetched as expected.
     */
    public function testCanFetchDepartures()
    {
        $infoFetcher = $this->getTestInstance("ANOTHERFAKE", array(), StationEvent::VEHICLE_BUS);

        $startTime = new DateTime("2020-01-20 14:20:00");
        $endTime = new DateTime("2020-01-20 14:45:00");

        // An API request should be triggered
        $this->rmvApiMock->expects($this->once())
                         ->method("doRequest")
                         ->with($this->callback(function(DepartureBoardRequest $_request){
                             $this->assertInstanceOf(DepartureBoardRequest::class, $_request);

                             $this->assertEquals(
                                 array(
                                     "extId" => "ANOTHERFAKE",
                                     "rtMode" => StationBoardRequest::RT_MODE_REALTIME,
                                     "date" => "2020-01-20",
                                     "time" => "14:20",
                                     "duration" => "25"
                                 ),
                                 $_request->getRequestParameters()
                             );

                             return true;
                         }))
                         ->will($this->returnValue(array("abfahrten" => "keine", "zahlen" => 0)));

        // The response of the API request should be parsed
        $this->infoParserMock->expects($this->once())
                             ->method("parseDepartureInfos")
                             ->with(array("abfahrten" => "keine", "zahlen" => 0), StationEvent::VEHICLE_BUS)
                             ->will($this->returnValue(array("#parsed-departure-infos#")));

        $arrivals = $infoFetcher->fetchDepartures($startTime, $endTime);
        $this->assertEquals(array("#parsed-departure-infos#"), $arrivals);
    }


    /**
     * Creates and returns a test instance of the RmvStationInfoFetcher.
     *
     * @param string $_stationId The station id
     * @param string[] $_ignoreLines The ignore lines
     * @param int $_vehicleType The vehicle type id
     *
     * @return RmvStationInfoFetcher The RmvStationInfoFetcher instance
     */
    private function getTestInstance(string $_stationId, array $_ignoreLines, int $_vehicleType): RmvStationInfoFetcher
    {
        return new RmvStationInfoFetcher(
            $this->rmvApiMock, $this->infoParserMock, $_stationId, $_ignoreLines, $_vehicleType
        );
    }
}
