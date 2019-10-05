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
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\Cache\CacheEntry;

/**
 * Provides a cache for the station infos.
 */
class StationInfoCache
{
    /**
     * The cache entry for this StationInfoCache
     *
     * @var CacheEntry $cacheEntry
     */
    private $cacheEntry;


    /**
     * StationInfoCache constructor.
     *
     * @param Cache $_cache The cache that will be used by this StationInfoCache
     * @param string $_className The name of the class for which this StationInfoCache will be used
     * @param string $_stationId The station id for which this StationInfoCache stores infos
     * @param int $_dataFetchInterval The data fetch interval in seconds
     */
    public function __construct(Cache $_cache, string $_className, string $_stationId, int $_dataFetchInterval)
    {
        $cacheEntryName = $_className . "/" . $_stationId . "/last-result.json";
        $this->cacheEntry = $_cache->getOrCreateEntry($cacheEntryName, $_dataFetchInterval);
    }


    /**
     * Returns the currently cached arrivals.
     * Will return false if no arrivals are cached at the moment.
     *
     * @param DateTime $_referenceTime The reference time that will be used to check if the cache is valid
     * @param DateTime $_startTime The start time for the arrivals
     * @param DateTime $_endTime The end time for the arrivals
     *
     * @return object[]|bool The cached arrivals
     */
    public function getArrivals(DateTime $_referenceTime, DateTime $_startTime, DateTime $_endTime)
    {
        return $this->loadCachedInfos("arrivals", $_referenceTime, $_startTime, $_endTime);
    }

    /**
     * Returns the currently cached departures.
     * Will return false if no departures are cached at the moment.
     *
     * @param DateTime $_referenceTime The reference time that will be used to check if the cache is valid
     * @param DateTime $_startTime The start time for the departures
     * @param DateTime $_endTime The end time for the departures
     *
     * @return object[]|bool The cached departures
     */
    public function getDepartures(DateTime $_referenceTime, DateTime $_startTime, DateTime $_endTime)
    {
        return $this->loadCachedInfos("departures", $_referenceTime, $_startTime, $_endTime);
    }

    /**
     * Sets the cached arrivals.
     *
     * @param DateTime $_referenceTime The reference time that will be used as cache timestamp
     * @param object[] $_arrivals The arrival objects
     */
    public function setArrivals(DateTime $_referenceTime, array $_arrivals)
    {
        $this->cacheInfos("arrivals", $_referenceTime, $_arrivals);
    }

    /**
     * Sets the cached departures.
     *
     * @param DateTime $_referenceTime The reference time that will be used as cache timestamp
     * @param object[] $_departures The departure objects
     */
    public function setDepartures($_referenceTime, $_departures)
    {
        $this->cacheInfos("departures", $_referenceTime, $_departures);
    }


    /**
     * Loads and returns the current cached infos.
     * Will return false if the current cached values are none of the specified type or if the entry is not valid.
     *
     * @param string $_type The info type (either "arrival" or "departure")
     * @param DateTime $_referenceTime The reference time
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return object[]|bool The cached infos
     */
    private function loadCachedInfos(string $_type, DateTime $_referenceTime, DateTime $_startTime, DateTime $_endTime)
    {
        if ($this->cacheEntry->isFor($_type) && $this->cacheEntry->isValid($_referenceTime))
        {
            $cachedInfos = $this->cacheEntry->getData();

            // Remove the cached infos that are not inside the target time span
            $cacheContainsInfosOutsideTimeSpan = false;
            $infos = array();
            foreach ($cachedInfos as $info)
            {
                if ($info->time >= $_startTime->format("H:i:s") && $info->time <= $_endTime->format("H:i:s"))
                {
                    $infos[] = $info;
                }
                else $cacheContainsInfosOutsideTimeSpan = true;
            }


            if ($cacheContainsInfosOutsideTimeSpan)
            {
                $this->cacheEntry->setData($infos)
                                 ->save();
            }

            return $infos;
        }
        else return false;
    }

    /**
     * Saves infos to the cache.
     *
     * @param string $_type The info type (either "arrival" or "departure")
     * @param DateTime $_referenceTime The reference time
     * @param object[] $_infos The infos to cache
     */
    private function cacheInfos(string $_type, DateTime $_referenceTime, array $_infos)
    {
        $this->cacheEntry->setData($_infos)
                         ->setFor($_type)
                         ->setCreateTimestamp($_referenceTime)
                         ->save();
    }
}
