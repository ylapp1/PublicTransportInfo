<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Util;

use DateTime;
use DateTimeZone;

/**
 * DateTime implementation that uses the timezone for Germany for every instance.
 */
class GermanDateTime extends DateTime
{
    /**
     * The timezone for Germany
     * @var DateTimeZone $timeZoneGermany
     */
    private static $timeZoneGermany;


    /**
     * GermanDateTime constructor.
     *
     * @param string $_time The time string
     */
    public function __construct($_time = "now")
    {
        parent::__construct($_time, self::getGermanyTimeZone());
    }


    /**
     * Creates and returns a GermanDateTime instance from a time string in a specified format.
     *
     * @param string $_format The format string
     * @param string $_time The time string
     * @param null $_timezone The timezone (will be ignored)
     *
     * @return DateTime|false The GermanDateTime instance
     */
    public static function createFromFormat($_format, $_time, $_timezone = null)
    {
        return parent::createFromFormat($_format, $_time, self::getGermanyTimeZone());
    }


    /**
     * Returns the timezone for Germany.
     *
     * @return DateTimeZone The timezone for Germany
     */
    private static function getGermanyTimeZone(): DateTimeZone
    {
        if (!isset(self::$timeZoneGermany))
        {
            self::$timeZoneGermany = new DateTimeZone("Europe/Berlin");
        }
        return self::$timeZoneGermany;
    }
}
