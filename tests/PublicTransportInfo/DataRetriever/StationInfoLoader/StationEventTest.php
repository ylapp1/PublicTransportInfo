<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;

/**
 * Checks that the StationEvent class works as expected.
 */
class StationEventTest extends TestCase
{

    /**
     * Checks that a StationEvent can be converted to a json object as expected.
     */
    public function testCanBeConvertedToJsonObject()
    {
        // StationEvent without delay
        $stationEventA = new StationEvent(
            new DateTime("15:00:00"), new DateTime("15:00:00"), "305", "Waldhausen",
            "Zielstadt", StationEvent::TYPE_ARRIVAL, StationEvent::VEHICLE_BUS
        );

        $expectedJsonObject = (object)array(
            "line" => "305",
            "time" => "15:00:00",
            "realTime" => "15:00:00",
            "delay" => 0,

            "arrivalStationName" => "Zielstadt",
            "departureStationName" => "Waldhausen",
            "eventType" => StationEvent::TYPE_ARRIVAL,
            "vehicleType" => StationEvent::VEHICLE_BUS
        );

        $this->assertEquals($expectedJsonObject, $stationEventA->toJsonObject());


        // StationEvent with delay
        $stationEventB = new StationEvent(
            new DateTime("12:20:00"), new DateTime("12:30:00"), "R621", "Große Stadt Hauptbahnhof",
            "Kleiner Ort", StationEvent::TYPE_DEPARTURE, StationEvent::VEHICLE_TRAIN
        );

        $expectedJsonObject = (object)array(
            "line" => "R621",
            "time" => "12:20:00",
            "realTime" => "12:30:00",
            "delay" => 10,

            "arrivalStationName" => "Kleiner Ort",
            "departureStationName" => "Große Stadt Hauptbahnhof",
            "eventType" => StationEvent::TYPE_DEPARTURE,
            "vehicleType" => StationEvent::VEHICLE_TRAIN
        );

        $this->assertEquals($expectedJsonObject, $stationEventB->toJsonObject());
    }
}
