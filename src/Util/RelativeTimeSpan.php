<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Util;

use DateInterval;
use DateTime;
use Exception;

/**
 * Provides methods to calculate a start and end time relative from a specified time.
 */
class RelativeTimeSpan
{
    /**
     * The duration to go into the past relative from a specified time
     * @var DateInterval $pastDuration
     */
    private $pastDuration;

    /**
     * The duration to go into the future relative from a specified time
     * @var DateInterval $futureDuration
     */
    private $futureDuration;

    /**
     * The minimum time that getStartTime() will return
     * @var DateTime $minimumTime
     */
    private $minimumTime;

    /**
     * The maximum time that getEndTime() will return
     * @var DateTime $maximumTime
     */
    private $maximumTime;


    /**
     * RelativeTimeSpan constructor.
     *
     * @param ConfigParser $_config The config
     */
    public function __construct(ConfigParser $_config)
    {
        $this->configure($_config);
    }


    /**
     * Configures this RelativeTimeSpan.
     *
     * @param ConfigParser $_config The config
     */
    private function configure(ConfigParser $_config)
    {
        $this->pastDuration = $this->createDateIntervalFromDurationString($_config->get("past", "0:0"));
        $this->futureDuration = $this->createDateIntervalFromDurationString($_config->get("future", "0:0"));

        $this->minimumTime = GermanDateTime::createFromFormat("H:i", $_config->get("min", "00:00"));
        $this->maximumTime = GermanDateTime::createFromFormat("H:i", $_config->get("max", "23:59"));
    }


    /**
     * Returns the start time of this time span relative to a specified time.
     *
     * @param DateTime $_relativeFrom The time for which to return the relative start time
     *
     * @return DateTime The relative start time
     */
    public function getStartTime(DateTime $_relativeFrom): DateTime
    {
        $startTime = clone $_relativeFrom;
        $startTime->sub($this->pastDuration);

        if ($startTime < $this->minimumTime) return clone $this->minimumTime;
        elseif ($startTime > $this->maximumTime) return clone $this->maximumTime;
        else return $startTime;
    }

    /**
     * Returns the end time of this time span relative to a specified time.
     *
     * @param DateTime $_relativeFrom The time for which to return the relative end time
     *
     * @return DateTime The relative end time
     */
    public function getEndTime(DateTime $_relativeFrom): DateTime
    {
        $endTime = clone $_relativeFrom;
        $endTime->add($this->futureDuration);

        if ($endTime > $this->maximumTime) return clone $this->maximumTime;
        elseif ($endTime < $this->minimumTime) return clone $this->minimumTime;
        else return $endTime;
    }


    /**
     * Creates and returns a DateInterval from a duration string in the format "hours:minutes".
     *
     * @param string $_durationString The duration string
     *
     * @return DateInterval The generated DateInterval
     *
     * @throws Exception The Exception when the duration string is not in the expected format
     */
    private function createDateIntervalFromDurationString(string $_durationString): DateInterval
    {
        if (preg_match("/^\d+:\d+$/", $_durationString) !== 1)
        { // Duration string does not match the "hours:minutes" format
            throw new Exception("Relative time span duration is not in expected format");
        }

        $durationParts = explode(":", $_durationString);
        return new DateInterval("PT" . (int)$durationParts[0] . "H" . (int)$durationParts[1] . "M");
    }
}
