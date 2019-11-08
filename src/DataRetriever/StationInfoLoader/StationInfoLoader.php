<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoader;

use DateTime;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\StationInfoFetcher;

/**
 * Provides methods to load infos about a station.
 */
class StationInfoLoader
{
    /**
     * The info fetcher that is used to fetch infos about a station
     * @var StationInfoFetcher $infoFetcher
     */
    private $infoFetcher;

    /**
     * The cache for fetched station infos
     *
     * @var StationInfoCache $cache
     */
    private $cache;


    /**
     * StationInfoLoader constructor.
     *
     * @param StationInfoCache $_cache
     * @param StationInfoFetcher $_infoFetcher
     */
	public function __construct(StationInfoCache $_cache, StationInfoFetcher $_infoFetcher)
	{
	    $this->cache = $_cache;
	    $this->infoFetcher = $_infoFetcher;
	}


    /**
     * Loads all arrivals for a specific time span.
     *
     * @param DateTime $_referenceTime The reference time. This is used to check if the cache can be used
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return object[] The arrivals
     */
    public function loadArrivals(DateTime $_referenceTime, DateTime $_startTime, DateTime $_endTime): array
    {
        if ($_endTime < $_startTime) return array();

        $arrivals = $this->cache->getArrivals( $_referenceTime, $_startTime, $_endTime);
        if ($arrivals === false)
        { // Cache is empty or expired
            $arrivals = $this->infoFetcher->fetchArrivals($_startTime, $_endTime);
            $arrivals = array_map(function(StationEvent $_arrival){
                return $_arrival->toJsonObject();
            }, $arrivals);
            $this->cache->setArrivals($_referenceTime, $arrivals);
        }

        return $arrivals;
    }

    /**
     * Loads all departures for a specific time span.
     *
     * @param DateTime $_referenceTime The reference time. This is used to check if the cache can be used
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return object[] The departures
     */
    public function loadDepartures(DateTime $_referenceTime, DateTime $_startTime, DateTime $_endTime): array
    {
        if ($_endTime < $_startTime) return array();

        $departures = $this->cache->getDepartures($_referenceTime, $_startTime, $_endTime);
        if ($departures === false)
        { // Cache is empty or expired
            $departures = $this->infoFetcher->fetchDepartures($_startTime, $_endTime);
            $departures = array_map(function(StationEvent $_departure){
                return $_departure->toJsonObject();
            }, $departures);
            $this->cache->setDepartures($_referenceTime, $departures);
        }

        return $departures;
    }
}
