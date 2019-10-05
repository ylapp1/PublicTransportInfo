<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher;

use DateTime;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;

/**
 * Fetches station infos from some source.
 */
abstract class StationInfoFetcher
{
    /**
     * The station id for which this StationInfoFetcher will load infos
     *
     * @var string $stationId
     */
    protected $stationId;

    /**
     * The lines to ignore from the arrivals and departures
     *
     * @var string[] $ignoreLines
     */
    protected $ignoreLines;

    /**
     * The vehicle type (one of the StationEvent::VEHICLE_* constants)
     *
     * @var int $vehicleType
     */
    protected $vehicleType;


    /**
     * StationInfoFetcher constructor.
     *
     * @param string $_stationId The station id
     * @param string[] $_ignoreLines The ignore lines
     * @param string $_vehicleType The vehicle type name
     */
    public function __construct(string $_stationId, array $_ignoreLines, string $_vehicleType)
    {
        $this->stationId = $_stationId;
        $this->ignoreLines = $_ignoreLines;
        $this->vehicleType = $_vehicleType;
    }


    /**
     * Fetches all arrivals for a specified arrival time span.
     *
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return StationEvent[] The arrivals
     */
    abstract public function fetchArrivals(DateTime $_startTime, DateTime $_endTime): array;

    /**
     * Fetches all departures for a specified arrival time span.
     *
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return StationEvent[] The departures
     */
    abstract public function fetchDepartures(DateTime $_startTime, DateTime $_endTime): array;
}
