<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoaderFactory;

use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoCache;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\StationInfoFetcher;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoLoader;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\ConfigParser;

/**
 * Handles the creation of StationInfoLoader objects.
 */
abstract class StationInfoLoaderFactory
{
    /**
     * The Cache that the StationInfoLoaders may use
     * @var Cache $cache
     */
    private $cache;

    /**
     * The data fetch interval in seconds
     * @var int $_dataFetchInterval
     */
    private $dataFetchInterval;

    /**
     * The identifier for this factory. This is used to create a cache key per StationInfoLoader
     * @var string $identifier
     */
    private $identifier;


    /**
     * StationInfoLoaderFactory constructor.
     *
     * @param string $_identifier The identifier for this factory
     * @param ConfigParser $_config The config
     * @param Cache $_cache The cache
     */
    public function __construct(string $_identifier, ConfigParser $_config, Cache $_cache)
    {
        $this->identifier = $_identifier;
        $this->cache = $_cache;
        $this->configure($_config);
    }

    /**
     * Configures this StationInfoLoader.
     *
     * @param ConfigParser $_config
     */
    protected function configure(ConfigParser $_config)
    {
        $this->dataFetchInterval = $_config->get("dataFetchInterval", 300);
    }


    /**
     * Creates and returns a new StationInfoLoader object.
     *
     * @param string $_stationId The station id
     * @param int $_vehicleType The vehicle type
     * @param array $_ignoreLines The ignore lines list
     *
     * @return StationInfoLoader The StationInfoLoader instance
     */
    public function createStationInfoLoader(string $_stationId, int $_vehicleType, array $_ignoreLines): StationInfoLoader
    {
        $loaderCache = new StationInfoCache($this->cache, $this->identifier, $_stationId, $this->dataFetchInterval);
        $infoFetcher = $this->getStationInfoFetcher($_stationId, $_ignoreLines, $_vehicleType);

        return new StationInfoLoader($loaderCache, $infoFetcher);
    }

    /**
     * Creates and returns a StationInfoFetcher instance for a StationInfoLoader.
     *
     * @param string $_stationId The station id
     * @param array $_ignoreLines The ignore lines list
     * @param int $_vehicleType The vehicle type
     *
     * @return StationInfoFetcher The StationInfoFetcher instance
     */
    abstract protected function getStationInfoFetcher(string $_stationId, array $_ignoreLines, int $_vehicleType): StationInfoFetcher;
}
