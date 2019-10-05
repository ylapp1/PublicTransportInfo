<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever;

use DateTime;
use Exception;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoLoader;
use PublicTransportInfo\DataRetriever\StationInfoLoaderFactory\StationInfoLoaderFactory;
use PublicTransportInfo\Util\ConfigParser;
use PublicTransportInfo\Util\RelativeTimeSpan;
use stdClass;

/**
 * Retrieves data for stations.
 */
class DataRetriever
{
    /**
     * The StationInfoLoader's that are used to retrieve data about the stations
     *
     * @var StationInfoLoader[] $stationInfoLoaders
     */
    private $stationInfoLoaders;

    /**
     * The arrival time span
     *
     * @var RelativeTimeSpan $arrivalTimeSpan
     */
    private $arrivalTimeSpan;

    /**
     * The departure time span
     *
     * @var RelativeTimeSpan $departureTimeSpan
     */
    private $departureTimeSpan;


    /**
     * DataRetriever constructor.
     *
     * @param RelativeTimeSpan $_arrivalTimeSpan The arrival time span
     * @param RelativeTimeSpan $_departureTimeSpan The departure time span
     */
    public function __construct(RelativeTimeSpan $_arrivalTimeSpan, RelativeTimeSpan $_departureTimeSpan)
    {
        $this->stationInfoLoaders = array();

        $this->arrivalTimeSpan = $_arrivalTimeSpan;
        $this->departureTimeSpan = $_departureTimeSpan;
    }


    /**
     * Retrieves all arrivals for a specified reference time.
     *
     * @param DateTime $_referenceTime The reference time
     *
     * @return stdClass[] The arrival info objects
     */
    public function retrieveArrivals(DateTime $_referenceTime): array
    {
        $startTime = $this->arrivalTimeSpan->getStartTime($_referenceTime);
        $endTime = $this->arrivalTimeSpan->getEndTime($_referenceTime);

        $infos = array();
        foreach ($this->stationInfoLoaders as $stationInfoLoader)
        {
            $stationInfos = $stationInfoLoader->loadArrivals($_referenceTime, $startTime, $endTime);
            $infos = array_merge($infos, $stationInfos);
        }

        return $infos;
    }

    /**
     * Retrieves all departures for a specified reference time.
     *
     * @param DateTime $_referenceTime The reference time
     *
     * @return stdClass[] The departure info objects
     */
    public function retrieveDepartures(DateTime $_referenceTime): array
    {
        $startTime = $this->departureTimeSpan->getStartTime($_referenceTime);
        $endTime = $this->departureTimeSpan->getEndTime($_referenceTime);

        $infos = array();
        foreach ($this->stationInfoLoaders as $stationInfoLoader)
        {
            $stationInfos = $stationInfoLoader->loadDepartures($_referenceTime, $startTime, $endTime);
            $infos = array_merge($infos, $stationInfos);
        }

        return $infos;
    }


    /**
     * Adds stations to this data retriever.
     *
     * @param ConfigParser $_config The config
     * @param StationInfoLoaderFactory $_factory The factory to use to create StationInfoLoader's
     *
     * @throws Exception The exception when the config is not valid
     */
    public function addStations(ConfigParser $_config, StationInfoLoaderFactory $_factory)
    {
        // Configure station ids
        $stations = $_config->get("stations", array());
        if (!is_array($stations)) throw new Exception("Station IDs property must be an array");

        $ignoreLines = $_config->get("ignoreLines", array());
        if (!is_array($ignoreLines)) throw new Exception("Ignore lines property must be an array");


        // Create the station info loaders
        foreach ($stations as $vehicleTypeName => $stationIds)
        {
            $vehicleType = $this->getVehicleTypeId($vehicleTypeName);
            foreach ($stationIds as $stationId)
            {
                $this->stationInfoLoaders[] = $_factory->createStationInfoLoader(
                    $stationId, $vehicleType, $ignoreLines
                );
            }
        }
    }


    /**
     * Returns the vehicle type id for a specified vehicle type name.
     *
     * @param string $_vehicleTypeName The vehicle type name
     *
     * @return int The vehicle type id
     *
     * @throws Exception The exception when no type exists for that vehicle tye name
     */
    private function getVehicleTypeId(string $_vehicleTypeName): int
    {
        if (strtolower($_vehicleTypeName) == "train") return StationEvent::VEHICLE_TRAIN;
        elseif (strtolower($_vehicleTypeName) == "bus") return StationEvent::VEHICLE_BUS;
        else throw new Exception("Invalid vehicle type \"" . $_vehicleTypeName . "\"");
    }
}
