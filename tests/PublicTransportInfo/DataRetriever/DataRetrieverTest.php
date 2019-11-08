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
use PublicTransportInfo\DataRetriever\DataRetriever;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoLoader;
use PublicTransportInfo\DataRetriever\StationInfoLoaderFactory\StationInfoLoaderFactory;
use PublicTransportInfo\Util\ConfigParser;
use PublicTransportInfo\Util\RelativeTimeSpan;

/**
 * Checks that the DataRetriever class works as expected.
 */
class DataRetrieverTest extends TestCase
{
    /**
     * Checks that an invalid value in the "stations" config property is handled as expected.
     *
     * @param mixed $_invalidStationsListValue The invalid "stations" config property value
     *
     * @dataProvider canDetectInvalidStationsListConfigDataProvider()
     */
    public function testCanDetectInvalidStationsListConfig($_invalidStationsListValue)
    {
        $retriever = new DataRetriever(
            $this->getRelativeTimeSpanMock("12:00:00", "13:00:00"),
            $this->getRelativeTimeSpanMock("14:00:00", "16:00:00")
        );

        $this->expectExceptionMessage("Station IDs property must be an array");
        $stationsConfig = new ConfigParser(array("stations" => $_invalidStationsListValue));
        $retriever->addStations($stationsConfig, $this->getStationInfoLoaderFactoryMock());
    }

    /**
     * Returns data sets for the testCanDetectInvalidStationsListConfig() test.
     * @return array The data sets
     */
    public function canDetectInvalidStationsListConfigDataProvider(): array
    {
        return array(array("notanarray"), array(51), array(57.3), array(false));
    }


    /**
     * Checks that an invalid value for the "ignoreLines" config property is handled as expected.
     *
     * @param mixed $_invalidIgnoreLinesValue The invalid "ignoreLines" config property value
     *
     * @dataProvider canDetectInvalidIgnoreLinesConfigDataProvider()
     */
    public function testCanDetectInvalidIgnoreLinesConfig($_invalidIgnoreLinesValue)
    {
        $retriever = new DataRetriever(
            $this->getRelativeTimeSpanMock("13:00:00", "13:30:00"),
            $this->getRelativeTimeSpanMock("14:00:00", "15:10:00")
        );

        $this->expectExceptionMessage("Ignore lines property must be an array");
        $stationsConfig = new ConfigParser(array("ignoreLines" => $_invalidIgnoreLinesValue));
        $retriever->addStations($stationsConfig, $this->getStationInfoLoaderFactoryMock());
    }

    /**
     * Returns data sets for the testCanDetectInvalidIgnoreLinesConfig() test.
     * @return array The data sets
     */
    public function canDetectInvalidIgnoreLinesConfigDataProvider()
    {
        return array(array("justastring"), array(9999), array(41.2), array(true));
    }


    /**
     * Checks that the vehicle type id is fetched from the vehicle names as expected.
     */
    public function testCanFetchVehicleTypeId()
    {
        $retriever = new DataRetriever(
            $this->getRelativeTimeSpanMock("13:00:00", "13:30:00"),
            $this->getRelativeTimeSpanMock("14:00:00", "15:10:00")
        );

        $stationsConfig = new ConfigParser(array(
            "stations" => array(
                "train" => array("abc"),
                "bus" => array("def"),
                "submarine" => array("wrong")
            ),
            "ignoreLines" => array("blub")
        ));

        $factoryMock = $this->getStationInfoLoaderFactoryMock();
        $factoryMock->expects($this->exactly(2))
                    ->method("createStationInfoLoader")
                    ->withConsecutive(
                        array("abc", StationEvent::VEHICLE_TRAIN, array("blub")),
                        array("def", StationEvent::VEHICLE_BUS, array("blub"))
                    )
                    ->will($this->returnValue($this->getStationInfoLoaderMock()));

        $this->expectExceptionMessage("Invalid vehicle type \"submarine\"");
        $retriever->addStations($stationsConfig, $factoryMock);
    }


    /**
     * Checks that arrivals can be retrieved as expected.
     */
    public function testCanRetrieveArrivals()
    {
        $retriever = new DataRetriever(
            $this->getRelativeTimeSpanMock("13:00:00", "13:30:00", "07:00:00"),
            $this->getRelativeTimeSpanMock("14:00:00", "15:10:00")
        );

        $stationsConfig = new ConfigParser(array(
            "stations" => array(
                "train" => array("R421", "R415"),
                "bus" => array("310")
            ),
            "ignoreLines" => array("308")
        ));

        $expectedMethodCall = "loadArrivals";
        $expectedParameters = array(new DateTime("07:00:00"), new DateTime("13:00:00"), new DateTime("13:30:00"));
        $factoryMockValueMap = array(
            array(
                "R421", StationEvent::VEHICLE_TRAIN, array("308"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array("bla", "blu"))
            ),
            array(
                "R415", StationEvent::VEHICLE_TRAIN, array("308"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array("ble", "bli"))
            ),
            array(
                "310", StationEvent::VEHICLE_BUS, array("308"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array("basdas"))
            )
        );

        $factoryMock = $this->getStationInfoLoaderFactoryMock();
        $factoryMock->expects($this->exactly(3))
                    ->method("createStationInfoLoader")
                    ->will($this->returnValueMap($factoryMockValueMap));

        $retriever->addStations($stationsConfig, $factoryMock);
        $stationInfos = $retriever->retrieveArrivals(new DateTime("07:00:00"));

        $expectedStationInfos = array("bla", "blu", "ble", "bli", "basdas");
        $this->assertEquals($expectedStationInfos, $stationInfos);
    }

    /**
     * Checks that departures can be retrieved as expected.
     */
    public function testCanRetrieveDepartures()
    {
        $retriever = new DataRetriever(
            $this->getRelativeTimeSpanMock("16:35:00", "16:36:00"),
            $this->getRelativeTimeSpanMock("12:47:00", "14:28:00", "13:10:00")
        );

        $stationsConfig = new ConfigParser(array(
            "stations" => array(
                "train" => array("R671"),
                "bus" => array("516", "862")
            ),
            "ignoreLines" => array("R48", "415")
        ));

        $expectedMethodCall = "loadDepartures";
        $expectedParameters = array(new DateTime("13:10:00"), new DateTime("12:47:00"), new DateTime("14:28:00"));
        $factoryMockValueMap = array(
            array(
                "R671", StationEvent::VEHICLE_TRAIN, array("R48", "415"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array("ein", "wert"))
            ),
            array(
                "516", StationEvent::VEHICLE_BUS, array("R48", "415"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array("zweites", "array", 8, 1))
            ),
            array(
                "862", StationEvent::VEHICLE_BUS, array("R48", "415"),
                $this->getStationInfoLoaderMock($expectedMethodCall, $expectedParameters, array(true))
            )
        );

        $factoryMock = $this->getStationInfoLoaderFactoryMock();
        $factoryMock->expects($this->exactly(3))
                    ->method("createStationInfoLoader")
                    ->will($this->returnValueMap($factoryMockValueMap));

        $retriever->addStations($stationsConfig, $factoryMock);
        $stationInfos = $retriever->retrieveDepartures(new DateTime("13:10:00"));

        $expectedStationInfos = array("ein", "wert", "zweites", "array", 8, 1, true);
        $this->assertEquals($expectedStationInfos, $stationInfos);
    }


    /**
     * Returns a RelativeTimeSpan mock.
     *
     * @param string $_startTimeString The start time that the mock will return
     * @param string $_endTimeString The end time that the mock will return
     * @param string $_referenceTime The reference time that the mock expects to be called with (optional)
     *
     * @return MockObject|RelativeTimeSpan The RelativeTimeSpan mock
     */
    private function getRelativeTimeSpanMock(string $_startTimeString, string $_endTimeString, string $_referenceTime = null): MockObject
    {
        $relativeTimeSpanMock = $this->getMockBuilder(RelativeTimeSpan::class)
                                     ->setMethods(array("getStartTime", "getEndTime"))
                                     ->disableOriginalConstructor()
                                     ->getMock();

        if ($_referenceTime)
        {
            $relativeTimeSpanMock->expects($this->once())
                                 ->method("getStartTime")
                                 ->with(new DateTime($_referenceTime))
                                 ->will($this->returnValue(new DateTime($_startTimeString)));

            $relativeTimeSpanMock->expects($this->once())
                                 ->method("getEndTime")
                                 ->with(new DateTime($_referenceTime))
                                 ->will($this->returnValue(new DateTime($_endTimeString)));
        }

        return $relativeTimeSpanMock;
    }

    /**
     * Returns a StationInfoLoaderFactory mock.
     * @return MockObject|StationInfoLoaderFactory The StationInfoLoaderFactory mock
     */
    private function getStationInfoLoaderFactoryMock(): MockObject
    {
        return $this->getMockBuilder(StationInfoLoaderFactory::class)
                    ->setMethods(array("createStationInfoLoader"))
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass();
    }

    /**
     * Returns a StationInfoLoader mock.
     *
     * @param string $_expectedMethodName The name of the method that the mock expects a call for (optional)
     * @param array $_expectedParameters The expected method call parameters (optional)
     * @param mixed $_mockedReturnValue The mocked return value for the expected method call
     *
     * @return MockObject|StationInfoLoader The StationInfoLoader mock
     */
    private function getStationInfoLoaderMock(string $_expectedMethodName = null, array $_expectedParameters = null, $_mockedReturnValue = null): MockObject
    {
        $stationInfoLoaderMock = $this->getMockBuilder(StationInfoLoader::class)
                                      ->setMethods(array("loadArrivals", "loadDepartures"))
                                      ->disableOriginalConstructor()
                                      ->getMock();

        if ($_expectedMethodName && $_expectedParameters && $_mockedReturnValue)
        {
            $stationInfoLoaderMock->expects($this->once())
                                  ->method($_expectedMethodName)
                                  ->with(...$_expectedParameters)
                                  ->will($this->returnValue($_mockedReturnValue));
        }

        return $stationInfoLoaderMock;
    }
}
