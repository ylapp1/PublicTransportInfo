<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Api\Rmv\Request;

use DateTime;
use Exception;
use PublicTransportInfo\Util\DateIntervalConverter;

/**
 * Base class for arrivalBoard and departureBoard requests.
 */
abstract class StationBoardRequest extends Request
{
    // Available realtime modes
    const RT_MODE_OFF = "OFF";
    const RT_MODE_INFOS = "INFOS";
    const RT_MODE_FULL = "FULL";
    const RT_MODE_REALTIME = "REALTIME";
    const RT_MODE_SERVER_DEFAULT = "SERVER_DEFAULT";


    /** @var string $id */
    private $id;

    /** @var string $extId */
    private $extId;

    /** @var string $direction */
    private $direction;

    /** @var DateTime $startDateTime */
    private $startDateTime;

    /** @var int $duration */
    private $duration;

    /** @var int $products */
    private $products;

    /** @var string[] $operators */
    private $operators;

    /** @var string[] $lines */
    private $lines;

    /** @var int $maxJourneys */
    private $maxJourneys;

    /** @var bool $filterEquiv */
    private $filterEquiv;

    /** @var string[] $attributes */
    private $attributes;

    /** @var string $rtMode */
    private $rtMode;


    /**
     * StationBoardRequest constructor.
     */
    public function __construct()
    {
        $this->products = 0;
        $this->operators = array();
        $this->lines = array();
        $this->attributes = array();
    }


    /**
     * Specifies the station/stop ID for which the departures or arrivals shall be retrieved.
     * Such ID can be retrieved from the location.name or location.nearbystops services.
     *
     * @param string $_id The station/stop ID
     *
     * @return StationBoardRequest The request instance
     */
    public function setId(string $_id): StationBoardRequest
    {
        $this->id = $_id;
        return $this;
    }

    /**
     * Specifies the external station/stop ID.
     * Such ID can be retrieved from the location.name or location.nearbystops services
     *
     * @param string $_extId The external station/stop ID
     *
     * @return StationBoardRequest The request instance
     */
    public function setExtId(string $_extId): StationBoardRequest
    {
        $this->extId = $_extId;
        return $this;
    }

    /**
     * If only vehicles departing or arriving from a certain direction shall be returned, specify the direction by
     * giving the station/stop ID of the last stop on the journey.
     *
     * @param string $_direction The station/stop ID of the last stop on the journey
     *
     * @return StationBoardRequest The request instance
     */
    public function setDirection(string $_direction): StationBoardRequest
    {
        $this->direction = $_direction;
        return $this;
    }

    /**
     * Sets the start date and time for which the departures or arrivals shall be retrieved.
     * Default: Current server date and time
     *
     * @param DateTime $_startDateTime The start date and time
     *
     * @return StationBoardRequest The request instance
     */
    public function setStartDateTime(DateTime $_startDateTime): StationBoardRequest
    {
        $this->startDateTime = $_startDateTime;
        return $this;
    }

    /**
     * Sets the interval size in minutes (Default: 60).
     * Value range: 0 - 1439
     *
     * Note: 0 will be treated as "infinite".
     *
     * @param int $_duration The interval size in minutes
     *
     * @return StationBoardRequest The request instance
     */
    public function setDuration(int $_duration): StationBoardRequest
    {
        if ($_duration < 0) $this->duration = 0;
        elseif ($_duration > 1439) $this->duration = 1439;
        else $this->duration = $_duration;

        return $this;
    }

    /**
     * Sets the end date and time for which the departures or arrivals shall be retrieved.
     * This is an alternative to setting the duration in minutes.
     *
     * @param DateTime $_endDateTime The end date and time
     *
     * @return StationBoardRequest The request instance
     */
    public function setEndDateTime(DateTime $_endDateTime): StationBoardRequest
    {
        $durationTimeInterval = $this->startDateTime->diff($_endDateTime);
        return $this->setDuration(DateIntervalConverter::toMinutes($durationTimeInterval));
    }


    /**
     * Decimal value defining the product classes to be included in the search. It represents a bitmask combining bit
     * number of a product as defined in the HAFAS raw data file zugart.
     * For example, regional trains are product class 2 and local trains are class 3, while busses are 4.
     * If you would like to search for local and regional trains only, you would need a bitmask where bits 2 and 3 are
     * set. You can pass it to this function as `2 | 3`.
     *
     * @param int $_productIds The product ids
     *
     * @return StationBoardRequest The request instance
     */
    public function addProducts(int $_productIds): StationBoardRequest
    {
        $this->products = $this->products | $_productIds;
        return $this;
    }

    /**
     * Only journeys provided by the given operators are part of the result.
     * To filter multiple operators, separate the codes by comma. If the operator should not be part of the result,
     * negated it by putting ! in front of it.
     * E.g. filter for A and B operator: operators=A,B.
     *
     * Value range: All operator codes or names from HAFAS raw data file betrieb.
     *
     * @param string $_operatorCode The operator code to include
     *
     * @return StationBoardRequest The request instance
     */
    public function includeOperator(string $_operatorCode): StationBoardRequest
    {
        $this->operators[] = $_operatorCode;
        return $this;
    }

    /**
     * Only journeys provided by the given operators are part of the result.
     * To filter multiple operators, separate the codes by comma. If the operator should not be part of the result,
     * negated it by putting ! in front of it.
     * E.g. filter for A and B operator: operators=A,B.
     *
     * @param string $_operatorCode The operator code to exclude
     *
     * @return StationBoardRequest The request instance
     */
    public function excludeOperator(string $_operatorCode): StationBoardRequest
    {
        $this->operators[] = "!" . $_operatorCode;
        return $this;
    }

    /**
     * Only journeys running the given line are part of the result.
     * To filter multiple lines, separate the codes by comma. If the line should not be included, negated it by putting
     * ! in front of it. E.g. filter for lines 120 and 140: lines=120,140
     *
     * @param string $_line The line to include
     *
     * @return StationBoardRequest The request instance
     */
    public function includeLine(string $_line): StationBoardRequest
    {
        $this->lines[] = $_line;
        return $this;
    }

    /**
     * Only journeys running the given line are part of the result.
     * To filter multiple lines, separate the codes by comma. If the line should not be included, negated it by putting
     * ! in front of it. E.g. filter for lines 120 and 140: lines=120,140
     *
     * @param string $_line The line to exclude
     *
     * @return StationBoardRequest The request instance
     */
    public function excludeLine(string $_line): StationBoardRequest
    {
        $this->lines[] = "!" . $_line;
        return $this;
    }

    /**
     * Maximum number of journeys to be returned.
     * If no value is defined, all departing/arriving services within the duration sized period are returned.
     * Please note: maxJourneys is not a hard limit. If the limit of maxJourneys is reached and there are additional
     * journeys that have the same departure/arrival time as the last journey within the limit (e.g. 14:57), those
     * additional journeys are also returned. This ensures that scrolling forward works by executing another
     * departure/arrival board request where the time is equal to the departure/arrival time of the last journey
     * increased by one minute (14:58 in our example).
     *
     * @param int $_maxJourneys The maximum number of journeys
     *
     * @return StationBoardRequest The request instance
     */
    public function setMaxJourneys(int $_maxJourneys): StationBoardRequest
    {
        $this->maxJourneys = $_maxJourneys;
        return $this;
    }

    /**
     * Enables/disables the filtering of equivalent marked stops (Default: 1).
     *
     * @param bool $_filterEquiv True to enable filtering of equivalent marked stops, false otherwise
     *
     * @return StationBoardRequest The request instance
     */
    public function setFilterEquiv(bool $_filterEquiv): StationBoardRequest
    {
        $this->filterEquiv = $_filterEquiv;
        return $this;
    }

    /**
     * Filter arriving or departing journeys by one or more attribute codes.
     * Multiple attribute codes are separated by comma. If the attribute should not be part of the journey,
     * negate it by putting ! in front of it.
     *
     * Value range: All attribute codes from HAFAS raw data.
     *
     * @param string $_attributeCode The attribute code to include
     *
     * @return StationBoardRequest The request instance
     */
    public function includeAttribute(string $_attributeCode): StationBoardRequest
    {
        $this->attributes[] = $_attributeCode;
        return $this;
    }

    /**
     * Filter arriving or departing journeys by one or more attribute codes.
     * Multiple attribute codes are separated by comma. If the attribute should not be part of the journey,
     * negate it by putting ! in front of it.
     *
     * Value range: All attribute codes from HAFAS raw data.
     *
     * @param string $_attributeCode The attribute code to exclude
     *
     * @return StationBoardRequest The request instance
     */
    public function excludeAttribute(string $_attributeCode): StationBoardRequest
    {
        $this->attributes[] = "!" . $_attributeCode;
        return $this;
    }

    /**
     * Sets the realtime mode to be used.
     * This must be one of the RT_MODE constants.
     *
     * @param string $_rtMode The realtime mode
     *
     * @return StationBoardRequest The request instance
     */
    public function setRtMode(string $_rtMode): StationBoardRequest
    {
        $this->rtMode = $_rtMode;
        return $this;
    }


    /**
     * Validates this Request.
     * Throws exceptions if this Request is not valid.
     *
     * @throws Exception The exception when no id and no extId are set
     * @throws Exception The exception when an invalid realtime mode was set
     */
    public function validate()
    {
        if (!isset($this->id) && !isset($this->extId))
        {
            throw new Exception("Id or extId must be set");
        }

        $availableRtModes = array(
            self::RT_MODE_OFF, self::RT_MODE_INFOS, self::RT_MODE_FULL, self::RT_MODE_REALTIME, self::RT_MODE_SERVER_DEFAULT
        );
        if (isset($this->rtMode) && !in_array($this->rtMode, $availableRtModes))
        {
            throw new Exception("Specified realtime mode \"" . $this->rtMode . "\" is invalid");
        }
    }


    /**
     * Generates and returns the HTTP request parameters as an associative array from this Request's configuration.
     *
     * @return mixed[] The request parameters
     */
    public function getRequestParameters(): array
    {
        $requestParameters = array();

        if (isset($this->id)) $requestParameters["id"] = $this->id;
        if (isset($this->extId)) $requestParameters["extId"] = $this->extId;
        if (isset($this->direction)) $requestParameters["direction"] = $this->direction;
        if (isset($this->startDateTime))
        {
            $requestParameters["date"] = $this->startDateTime->format("Y-m-d");
            $requestParameters["time"] = $this->startDateTime->format("H:i");
        }
        if (isset($this->duration)) $requestParameters["duration"] = $this->duration;
        if ($this->products > 0) $requestParameters["products"] = $this->products;
        if (count($this->operators) > 0)
        {
            $requestParameters["operators"] = join(",", $this->operators);
        }
        if (count($this->lines) > 0)
        {
            $requestParameters["lines"] = join(",", $this->lines);
        }
        if (isset($this->maxJourneys)) $requestParameters["maxJourneys"] = $this->maxJourneys;
        if (isset($this->filterEquiv))
        {
            $requestParameters["filterEquiv"] = $this->filterEquiv ? 1 : 0;
        }
        if (count($this->attributes) > 0)
        {
            $requestParameters["attributes"] = join(",", $this->attributes);
        }
        if (isset($this->rtMode)) $requestParameters["rtMode"] = $this->rtMode;

        return $requestParameters;
    }
}
