<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoaderFactory;

use Exception;
use GuzzleHttp\Client;
use PublicTransportInfo\Api\RmvApi;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser\RmvInfoParser;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\RmvStationInfoFetcher;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\StationInfoFetcher;
use PublicTransportInfo\Util\Cache\Cache;
use PublicTransportInfo\Util\ConfigParser;

/**
 * Handles the creation of StationInfoLoader objects that use the RMV API to load station infos.
 */
class RmvStationInfoLoaderFactory extends StationInfoLoaderFactory
{
    /**
     * The RmvApi that is passed to new RmvStationInfoFetcher instances
     * @var RmvApi $rmvApi
     */
    private $rmvApi;

    /**
     * The info parser that is passed to new RmvStationInfoFetcher instances
     * @var RmvInfoParser $rmvInfoParser
     */
    private $infoParser;


    /**
     * RmvStationInfoLoaderFactory constructor.
     *
     * @param ConfigParser $_config The config
     * @param Cache $_cache The cache
     */
    public function __construct(ConfigParser $_config, Cache $_cache)
    {
        parent::__construct("rmv", $_config, $_cache);
        $this->infoParser = new RmvInfoParser();
    }


    /**
     * Configures this StationInfoLoader.
     *
     * @param ConfigParser $_config The config
     *
     * @throws Exception The exception when the config is not valid
     */
    protected function configure(ConfigParser $_config)
    {
        parent::configure($_config);

        $apiToken = $_config->get("apiToken", false);
        if (!$apiToken)
        {
            throw new Exception("No api token specified for RmvStationInfoLoaderFactory");
        }

        $this->rmvApi = new RmvApi(new Client(), $apiToken);
    }


    /**
     * Creates and returns a RmvStationInfoFetcher instance for a StationInfoLoader.
     *
     * @param string $_stationId The station id
     * @param array $_ignoreLines The ignore lines list
     * @param int $_vehicleType The vehicle type
     *
     * @return RmvStationInfoFetcher The RmvStationInfoFetcher instance
     */
    protected function getStationInfoFetcher(string $_stationId, array $_ignoreLines, int $_vehicleType): StationInfoFetcher
    {
        return new RmvStationInfoFetcher(
            $this->rmvApi, $this->infoParser, $_stationId, $_ignoreLines, $_vehicleType
        );
    }
}
