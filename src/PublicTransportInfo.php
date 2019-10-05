<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo;

use DateTime;
use Exception;
use PublicTransportInfo\DataRetriever\DataRetriever;
use PublicTransportInfo\Util\GermanDateTime;
use PublicTransportInfo\Util\RelativeTimeSpan;
use PublicTransportInfo\Util\ConfigParser;
use stdClass;

/**
 * Wrapper class for the PublicTransportInfo backend.
 */
class PublicTransportInfo
{
    /**
     * The config
     * @var ConfigParser $config
     */
    private $config;

    /**
     * The display mode selector
     * @var DisplayModeSelector $displayModeSelector
     */
    private $displayModeSelector;

    /**
     * The data retriever
     * @var DataRetriever $dataRetriever
     */
    private $dataRetriever;


    /**
     * PublicTransportInfo constructor.
     * @param string $_configFilePath The path to the config file
     */
    public function __construct(string $_configFilePath)
    {
        $this->config = new ConfigParser(require_once($_configFilePath));
        $this->displayModeSelector = new DisplayModeSelector(
            new ConfigParser($this->config->get("displayTimeSpan", array()))
        );
    }


    /**
     * Returns the infos for a specified reference time.
     * If none is specified the current time will be used.
     *
     * @param DateTime $_referenceTime The reference time
     *
     * @return stdClass[] The infos
     */
    public function getInfos(DateTime $_referenceTime = null): array
    {
        try
        {
            if ($_referenceTime === null)
            {
                $referenceTime = new GermanDateTime("now");
            }
            else $referenceTime = $_referenceTime;

            $displayMode = $this->displayModeSelector->getDisplayMode($referenceTime);
            if ($displayMode == DisplayModeSelector::DISPLAY_NONE) $infos = array();
            else
            {
                $this->initializeDataRetriever();

                if ($displayMode == DisplayModeSelector::DISPLAY_ARRIVAL)
                {
                    $infos = $this->dataRetriever->retrieveArrivals($referenceTime);
                }
                elseif ($displayMode == DisplayModeSelector::DISPLAY_DEPARTURE)
                {
                    $infos = $this->dataRetriever->retrieveDepartures($referenceTime);
                }


                // Sort the infos by time
                usort($infos, function($_infoA, $_infoB){
                    return $_infoA->time > $_infoB->time;
                });
            }

            return $infos;
        }
        catch(Exception $_exception)
        {
            return array((object)array("errorMessage" => $_exception->getMessage()));
        }
    }


    /**
     * Initializes the DataRetriever based on the config.
     */
    private function initializeDataRetriever()
    {
        $this->dataRetriever = $this->createDataRetriever();

        $cacheDirectory = $this->config->get("cacheDirectory", sys_get_temp_dir() . "/public-transport-info");
        $cache = new Util\Cache\Cache($cacheDirectory);

        foreach ($this->config->get("dataSources", array()) as $factoryType => $config)
        {
            $config = new ConfigParser($config);
            $factoryClass = "PublicTransportInfo\\DataRetriever\\StationInfoLoaderFactory\\" . $factoryType . "StationInfoLoaderFactory";
            if (class_exists($factoryClass))
            {
                // Initialize the factory
                $factoryConfig = new ConfigParser($config->get("factoryConfig", array()));
                $factory = new $factoryClass($factoryConfig, $cache);

                $stationsConfig = new ConfigParser($config->get("stationInfoConfig", array()));

                $this->dataRetriever->addStations($stationsConfig, $factory);
            }
            else throw new Exception("No StationInfoLoaderFactory class exists for type '" . $factoryType . "'");
        }
    }

    /**
     * Creates and returns a new DataRetriever.
     *
     * @return DataRetriever The DataRetriever instance
     */
    private function createDataRetriever(): DataRetriever
    {
        $arrivalTimeSpan = new RelativeTimeSpan(
            new ConfigParser($this->config->get("arrivalTimeSpan", array()))
        );
        $departureTimeSpan = new RelativeTimeSpan(
            new ConfigParser($this->config->get("departureTimeSpan", array()))
        );

        return new DataRetriever($arrivalTimeSpan, $departureTimeSpan);
    }
}
