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

/**
 * Provides methods to convert DateInterval's to numbers of minutes or seconds.
 */
class DateIntervalConverter
{
    /**
     * Converts a DateInterval to the number of seconds that are represented by it.
     *
     * @param DateInterval $_dateInterval The DateInterval to convert
     *
     * @return int The number of seconds
     */
    public static function toSeconds(DateInterval $_dateInterval): int
    {
        return (new DateTime())->setTimeStamp(0)
                               ->add($_dateInterval)
                               ->getTimeStamp();
    }

    /**
     * Converts a DateInterval to the number of minutes that are represented by it.
     *
     * @param DateInterval $_dateInterval The DateInterval to convert
     *
     * @return int The number of minutes
     */
    public static function toMinutes(DateInterval $_dateInterval): int
    {
        return round(self::toSeconds($_dateInterval) / 60);
    }
}
