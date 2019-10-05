<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser;

use DateTime;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Parses fetched infos from the RMV API into StationEvent objects.
 */
class RmvInfoParser extends InfoParser
{
    /**
     * Parses arrival infos into StationEvent objects.
     *
     * @param array $_arrivalInfos The decoded arrival info json response
     * @param int $_vehicleType The vehicle type of the arrivals
     *
     * @return StationEvent[] The list of StationEvent's
     */
    public function parseArrivalInfos(array $_arrivalInfos, int $_vehicleType): array
    {
        $arrivals = array();
        if (isset($_arrivalInfos["Arrival"]))
        {
            foreach ($_arrivalInfos["Arrival"] as $arrivalInfoJson)
            {
                $arrival = $this->createStationEventFromArrivalInfo($arrivalInfoJson, $_vehicleType);
                $arrivals[] = $arrival;
            }
        }

        return $arrivals;
    }

    /**
     * Parses departure infos into StationEvent objects.
     *
     * @param array $_departureInfos The decoded departure info json response
     * @param int $_vehicleType The vehicle type of the departures
     *
     * @return StationEvent[] The list of StationEvent's
     */
    public function parseDepartureInfos(array $_departureInfos, int $_vehicleType): array
    {
        $departures = array();
        if (isset($_departureInfos["Departure"]))
        {
            foreach($_departureInfos["Departure"] as $departureInfoJson)
            {
                $departure = $this->createStationEventFromDepartureInfo($departureInfoJson, $_vehicleType);
                $departures[] = $departure;
            }
        }

        return $departures;
    }


    /**
     * Creates and returns a StationEvent from RMV arrival json data.
     *
     * @param array $_arrivalInfoJson The arrival info json
     * @param int $_vehicleType The vehicle type of the departures
     *
     * @return StationEvent The StationEvent
     */
    private function createStationEventFromArrivalInfo(array $_arrivalInfoJson, int $_vehicleType): StationEvent
    {
        $time = $this->getTime($_arrivalInfoJson);
        $realTime = $this->getRealTime($_arrivalInfoJson);
        if (!$realTime) $realTime = $time;

        $lineName = $_arrivalInfoJson["Product"]["line"];
        $departureStationName = $_arrivalInfoJson["origin"];
        $arrivalStationName = $_arrivalInfoJson["stop"];
        $eventType = StationEvent::TYPE_ARRIVAL;

        return new StationEvent($time, $realTime, $lineName, $departureStationName, $arrivalStationName, $eventType, $_vehicleType);
    }

    /**
     * Creates and returns a StationEvent from RMV departure json data.
     *
     * @param array $_departureInfoJson The departure info json
     * @param int $_vehicleType The vehicle type of the departures
     *
     * @return StationEvent The StationEvent instance
     */
    private function createStationEventFromDepartureInfo(array $_departureInfoJson, int $_vehicleType): StationEvent
    {
        $time = $this->getTime($_departureInfoJson);
        $realTime = $this->getRealTime($_departureInfoJson);
        if (!$realTime) $realTime = $time;

        $lineName = $_departureInfoJson["Product"]["line"];
        $departureStationName = $_departureInfoJson["stop"];
        $arrivalStationName = $_departureInfoJson["direction"];
        $eventType = StationEvent::TYPE_DEPARTURE;

        return new StationEvent($time, $realTime, $lineName, $departureStationName, $arrivalStationName, $eventType, $_vehicleType);
    }


    /**
     * Returns the time of arrival or departure json data as a DateTime instance.
     *
     * @param array $_json The json data
     *
     * @return DateTime The time
     */
    private function getTime(array $_json): DateTime
    {
        return GermanDateTime::createFromFormat("Y-m-dH:i:s", $_json["date"] . $_json["time"]);
    }

    /**
     * Returns the real time of arrival or departure json data as a DateTime instance.
     * Null will be returned if no real time is defined.
     *
     * @param array $_json The json data
     *
     * @return DateTime|null The real time
     */
    private function getRealTime(array $_json)
    {
        if (isset($_json["rtDate"]) && isset($_json["rtTime"]))
        {
            return GermanDateTime::createFromFormat("Y-m-dH:i:s", $_json["rtDate"] . $_json["rtTime"]);
        }
        else return null;
    }
}
