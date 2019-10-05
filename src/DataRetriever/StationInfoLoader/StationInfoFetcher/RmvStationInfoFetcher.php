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
use GuzzleHttp\Exception\GuzzleException;
use PublicTransportInfo\Api\Rmv\Request\ArrivalBoardRequest;
use PublicTransportInfo\Api\Rmv\Request\DepartureBoardRequest;
use PublicTransportInfo\Api\Rmv\Request\StationBoardRequest;
use PublicTransportInfo\Api\RmvApi;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationInfoFetcher\InfoParser\RmvInfoParser;
use PublicTransportInfo\DataRetriever\StationInfoLoader\StationEvent;

/**
 * Fetches station infos from the RMV api.
 */
class RmvStationInfoFetcher extends StationInfoFetcher
{
    /**
     * The RMV api
     *
     * @var RmvApi $rmvApi
     */
    private $rmvApi;

    /**
     * The info parser that is used to translate fetched infos to StationEvent objects
     *
     * @var RmvInfoParser $infoParser
     */
    private $infoParser;


    /**
     * RmvStationInfoFetcher constructor.
     *
     * @param RmvApi $_rmvApi The RMV api
     * @param RmvInfoParser $_infoParser
     * @param string $_stationId The station id
     * @param string[] $_ignoreLines The ignore lines
     * @param string $_vehicleType The vehicle type name
     */
    public function __construct(RmvApi $_rmvApi, RmvInfoParser $_infoParser, string $_stationId, array $_ignoreLines, string $_vehicleType)
    {
        parent::__construct($_stationId, $_ignoreLines, $_vehicleType);
        $this->rmvApi = $_rmvApi;
        $this->infoParser = $_infoParser;
    }


    /**
     * Fetches all arrivals for a specified arrival time span.
     *
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return StationEvent[] The arrivals
     *
     * @throws GuzzleException
     */
    public function fetchArrivals(DateTime $_startTime, DateTime $_endTime): array
    {
        $request = new ArrivalBoardRequest();
        $this->configureGeneralRequestOptions($request, $_startTime, $_endTime);
        $arrivalsJsonResponse = $this->rmvApi->doRequest($request);

        return $this->infoParser->parseArrivalInfos($arrivalsJsonResponse, $this->vehicleType);
    }

    /**
     * Fetches all departures for a specified arrival time span.
     *
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     *
     * @return StationEvent[] The departures
     *
     * @throws GuzzleException
     */
    public function fetchDepartures(DateTime $_startTime, DateTime $_endTime): array
    {
        $request = new DepartureBoardRequest();
        $this->configureGeneralRequestOptions($request, $_startTime, $_endTime);
        $departuresJsonResponse = $this->rmvApi->doRequest($request);

        return $this->infoParser->parseDepartureInfos($departuresJsonResponse, $this->vehicleType);
    }


    /**
     * Configures the options for a RmvApi request that are exactly the same for arrival and departure requests.
     *
     * @param StationBoardRequest $_request The request to configure
     * @param DateTime $_startTime The start time
     * @param DateTime $_endTime The end time
     */
    private function configureGeneralRequestOptions(StationBoardRequest $_request, DateTime $_startTime, DateTime $_endTime)
    {
        $_request->setExtId($this->stationId)
                 ->setRtMode(StationBoardRequest::RT_MODE_REALTIME)
                 ->setStartDateTime($_startTime)
                 ->setEndDateTime($_endTime);

        foreach ($this->ignoreLines as $ignoreLine)
        {
            $_request->excludeLine($ignoreLine);
        }
    }
}
