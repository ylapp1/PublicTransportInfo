<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser\RmvInfoParser;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;

/**
 * Checks that the RmvInfoParser class works as expected.
 */
class RmvInfoParserTest extends TestCase
{
    /**
     * The path to the directory that contains example RMV API responses
     * @var string $exampleResponsesDirectoryPath
     */
    private $exampleResponsesDirectoryPath;


    /**
     * RmvInfoParserTest constructor.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->exampleResponsesDirectoryPath = __DIR__ . "/../../../../../data/example_responses/Rmv";
    }


    /**
     * Checks if empty arrival responses are parsed as expected.
     */
    public function testCanParseEmptyArrivalResponse()
    {
        $infoParser = new RmvInfoParser();
        $parsedInfos = $infoParser->parseArrivalInfos($this->getExampleResponse("no_results.json"), StationEvent::VEHICLE_BUS);

        $this->assertCount(0, $parsedInfos);
    }

    /**
     * Checks if empty departure responses are parsed as expected.
     */
    public function testCanParseEmptyDepartureResponse()
    {
        $infoParser = new RmvInfoParser();
        $parsedInfos = $infoParser->parseDepartureInfos($this->getExampleResponse("no_results.json"), StationEvent::VEHICLE_TRAIN);

        $this->assertCount(0, $parsedInfos);
    }

    /**
     * Checks that arrivals are parsed as expected including arrivals with and without delay.
     */
    public function testCanParseArrivals()
    {
        $infoParser = new RmvInfoParser();
        $parsedInfos = $infoParser->parseArrivalInfos($this->getExampleResponse("arrivalBoard.json"), StationEvent::VEHICLE_BUS);

        $this->assertCount(6, $parsedInfos);

        $expectedInfos = array(
            (object)array(
                "line" => "302",
                "time" => "17:17:00",
                "realTime" => "17:17:00",
                "delay" => 0,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Dietzhölztal-Mandeln Wendeplatz",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            ),
            (object)array(
                "line" => "101",
                "time" => "17:26:00",
                "realTime" => "17:26:00",
                "delay" => 0,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Dillenburg-Donsbach Grubstraße",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            ),
            (object)array(
                "line" => "102",
                "time" => "17:28:00",
                "realTime" => "17:28:00",
                "delay" => 0,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Dillenburg-Manderbach Friedhof",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            ),
            (object)array(
                "line" => "101",
                "time" => "17:29:00",
                "realTime" => "17:29:00",
                "delay" => 0,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Eschenburg-Hirzenhain Bahnhof",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            ),
            (object)array(
                "line" => "302",
                "time" => "17:41:00",
                "realTime" => "17:41:00",
                "delay" => 0,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Dietzhölztal-Mandeln Wendeplatz",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            ),
            (object)array(
                "line" => "491",
                "time" => "17:55:00",
                "realTime" => "17:57:00",
                "delay" => 2,

                "arrivalStationName" => "Dillenburg ZOB",
                "departureStationName" => "Biedenkopf Marktplatz",
                "eventType" => StationEvent::TYPE_ARRIVAL,
                "vehicleType" => StationEvent::VEHICLE_BUS
            )
        );

        $infoJsonObjects = array_map(function(StationEvent $_parsedInfo){
            return $_parsedInfo->toJsonObject();
        }, $parsedInfos);

        $this->assertEquals($expectedInfos, $infoJsonObjects);
    }

    /**
     * Checks that departures are parsed as expected including departures with and without delay.
     */
    public function testCanParseDeparturesFromDepartureResponse()
    {
        $infoParser = new RmvInfoParser();
        $parsedInfos = $infoParser->parseDepartureInfos($this->getExampleResponse("departureBoard.json"), StationEvent::VEHICLE_TRAIN);

        $this->assertCount(4, $parsedInfos);

        $expectedInfos = array(
            (object)array(
                "line" => "RE99",
                "time" => "09:38:00",
                "realTime" => "09:42:00",
                "delay" => 4,

                "arrivalStationName" => "Siegen Hauptbahnhof",
                "departureStationName" => "Dillenburg Bahnhof",
                "eventType" => StationEvent::TYPE_DEPARTURE,
                "vehicleType" => StationEvent::VEHICLE_TRAIN
            ),
            (object)array(
                "line" => "RB95",
                "time" => "09:47:00",
                "realTime" => "09:47:00",
                "delay" => 0,

                "arrivalStationName" => "Siegen Hauptbahnhof",
                "departureStationName" => "Dillenburg Bahnhof",
                "eventType" => StationEvent::TYPE_DEPARTURE,
                "vehicleType" => StationEvent::VEHICLE_TRAIN
            ),
            (object)array(
                "line" => "RB96",
                "time" => "10:16:00",
                "realTime" => "10:16:00",
                "delay" => 0,

                "arrivalStationName" => "Betzdorf (Sieg) Bahnhof",
                "departureStationName" => "Dillenburg Bahnhof",
                "eventType" => StationEvent::TYPE_DEPARTURE,
                "vehicleType" => StationEvent::VEHICLE_TRAIN
            ),
            (object)array(
                "line" => "RE99",
                "time" => "10:17:00",
                "realTime" => "10:17:00",
                "delay" => 0,

                "arrivalStationName" => "Frankfurt (Main) Hauptbahnhof",
                "departureStationName" => "Dillenburg Bahnhof",
                "eventType" => StationEvent::TYPE_DEPARTURE,
                "vehicleType" => StationEvent::VEHICLE_TRAIN
            )
        );

        $infoJsonObjects = array_map(function(StationEvent $_parsedInfo){
            return $_parsedInfo->toJsonObject();
        }, $parsedInfos);

        $this->assertEquals($expectedInfos, $infoJsonObjects);
    }


    /**
     * Json decodes and returns one of the example responses.
     *
     * @param string $_fileName The file name of the example response
     *
     * @return array The json decoded example response
     */
    private function getExampleResponse(string $_fileName): array
    {
        $jsonString = file_get_contents($this->exampleResponsesDirectoryPath . "/" . $_fileName);
        return json_decode($jsonString, true);
    }
}
