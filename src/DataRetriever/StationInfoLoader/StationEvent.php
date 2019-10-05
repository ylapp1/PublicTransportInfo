<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\DataRetriever\StationInfoLoader;

use DateInterval;
use DateTime;
use PublicTransportInfo\Util\DateIntervalConverter;
use stdClass;

/**
 * Stores the information about a arrival or departure at a station.
 */
class StationEvent
{
    // StationEvent types
    const TYPE_ARRIVAL = 1;
    const TYPE_DEPARTURE = 2;

    // Vehicle types
    const VEHICLE_TRAIN = 3;
    const VEHICLE_BUS = 4;


    /**
     * The planned arrival/departure time
     * @var DateTime $time
     */
	private $time;

	/**
     * The predicted arrival/departure time (there is a delay if this is higher than the planned arrival/departure time)
     * @var DateTime $realTime
     */
	private $realTime;

	/**
     * The delay between planned and predicted arrival/departure time
     * @var DateInterval $delay
     */
    private $delay;

    /**
     * The name of the line
     * @var string $lineName
     */
    private $lineName;

    /**
     * The name of the departure station
     * @var string $departureStationName
     */
    private $departureStationName;

    /**
     * The name of the arrival station
     * @var string $arrivalStationName
     */
    private $arrivalStationName;

    /**
     * The event type (one of the "TYPE_" constants)
     * @var int $eventType
     */
    private $eventType;

    /**
     * The vehicle type (one of the "VEHICLE_" constants)
     * @var int $vehicleType
     */
    private $vehicleType;


	/**
	 * StationEvent constructor.
	 *
	 * @param DateTime $_time The planned arrival/departure time
	 * @param DateTime $_realTime The predicted arrival/departure time
     * @param string $_lineName The name of the line
     * @param string $_departureStationName The name of the departure station
     * @param string $_arrivalStationName The name of the arrival station
     * @param int $_eventType The event type (one of the "TYPE_" constants)
     * @param int $_vehicleType The vehicle type (one of the "VEHICLE_" constants)
	 */
	public function __construct(DateTime $_time, DateTime $_realTime, string $_lineName, string $_departureStationName, string $_arrivalStationName, int $_eventType, int $_vehicleType)
	{
		$this->time = $_time;
		$this->realTime = $_realTime;
        $this->delay = $_time->diff($_realTime);

        $this->lineName = $_lineName;
        $this->departureStationName  = $_departureStationName;
        $this->arrivalStationName = $_arrivalStationName;
        $this->eventType = $_eventType;
        $this->vehicleType = $_vehicleType;
	}


    /**
     * Returns a json object representation of this StationEvent.
     *
     * @return stdClass The json object
     */
    public function toJsonObject()
    {
        $jsonObject = (object)array(
            "line" => $this->lineName,
            "time" => $this->time->format("H:i:s"),
            "realTime" => $this->realTime->format("H:i:s"),
            "delay" => DateIntervalConverter::toMinutes($this->delay),

            "arrivalStationName" => $this->arrivalStationName,
            "departureStationName" => $this->departureStationName,
            "eventType" => $this->eventType,
            "vehicleType" => $this->vehicleType
        );

        return $jsonObject;
    }
}
