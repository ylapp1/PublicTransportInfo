<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser;

use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;

/**
 * Parses fetched infos into StationEvent objects.
 */
abstract class InfoParser
{
    /**
     * Parses arrival infos into StationEvent objects.
     *
     * @param mixed $_arrivalInfos The received arrival infos
     * @param int $_vehicleType The vehicle type of the arrivals
     *
     * @return StationEvent[] The list of StationEvent's
     */
    abstract public function parseArrivalInfos(array $_arrivalInfos, int $_vehicleType): array;

    /**
     * Parses departure infos into StationEvent objects.
     *
     * @param mixed $_departureInfos The received departure infos
     * @param int $_vehicleType The vehicle type of the departures
     *
     * @return StationEvent[] The list of StationEvent's
     */
    abstract public function parseDepartureInfos(array $_departureInfos, int $_vehicleType): array;
}
