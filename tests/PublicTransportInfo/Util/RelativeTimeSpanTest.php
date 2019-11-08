<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Util\ConfigParser;
use PublicTransportInfo\Util\GermanDateTime;
use PublicTransportInfo\Util\RelativeTimeSpan;

/**
 * Checks that the RelativeTimeSpan class works as expected.
 */
class RelativeTimeSpanTest extends TestCase
{
    /**
     * Checks that an invalid "past" duration string is detected.
     *
     * @param string $_invalidDurationString The invalid duration string to set
     *
     * @dataProvider invalidDurationStringDataProvider()
     */
    public function testDetectsInvalidPastDurationString(string $_invalidDurationString)
    {
        $relativeTimeSpanConfig = new ConfigParser(array(
            "past" => $_invalidDurationString
        ));

        $this->expectExceptionMessage("Relative time span duration is not in expected format");
        new RelativeTimeSpan($relativeTimeSpanConfig);
    }

    /**
     * Checks that an invalid "future" duration string is detected.
     *
     * @param string $_invalidDurationString The invalid duration string to set
     *
     * @dataProvider invalidDurationStringDataProvider()
     */
    public function testDetectsInvalidFutureDurationString(string $_invalidDurationString)
    {
        $relativeTimeSpanConfig = new ConfigParser(array(
            "future" => $_invalidDurationString
        ));

        $this->expectExceptionMessage("Relative time span duration is not in expected format");
        new RelativeTimeSpan($relativeTimeSpanConfig);
    }

    /**
     * Returns data sets that contain an invalid duration string.
     * @return array The data sets
     */
    public function invalidDurationStringDataProvider(): array
    {
        return array(
            array("5:"), array(":7"), array("4567"), array(""), array(":"), array("notanumber"), array("hours:minutes")
        );
    }


    /**
     * Checks that the start and end time can be returned relative to a specified time.
     */
    public function testCanReturnStartAndEndTimeRelativeToSpecifiedTime()
    {
        // Custom config
        $relativeTimeSpanConfig = new ConfigParser(array(
            "past" => "1:5",
            "future" => "2:20",
            "min" => "08:00",
            "max" => "18:00"
        ));

        $timeSpan = new RelativeTimeSpan($relativeTimeSpanConfig);

        // Time in between start and end date
        $timeA = new GermanDateTime("13:00:00");
        $this->assertEquals(new GermanDateTime("11:55:00"), $timeSpan->getStartTime($timeA));
        $this->assertEquals(new GermanDateTime("15:20:00"), $timeSpan->getEndTime($timeA));

        // Time whose relative start time is below minimum
        $timeB = new GermanDateTime("08:30:00");
        $this->assertEquals(new GermanDateTime("08:00:00"), $timeSpan->getStartTime($timeB));
        $this->assertEquals(new GermanDateTime("10:50:00"), $timeSpan->getEndTime($timeB));

        // Time whose relative end time is above maximum
        $timeC = new GermanDateTime("16:55:00");
        $this->assertEquals(new GermanDateTime("15:50:00"), $timeSpan->getStartTime($timeC));
        $this->assertEquals(new GermanDateTime("18:00:00"), $timeSpan->getEndTime($timeC));

        // Time whose relative end time is below minimum
        $timeD = new GermanDateTime("05:15:00");
        $this->assertEquals(new GermanDateTime("08:00:00"), $timeSpan->getStartTime($timeD));
        $this->assertEquals(new GermanDateTime("08:00:00"), $timeSpan->getEndTime($timeD));

        // Time whose relative start time is above maximum
        $timeE = new GermanDateTime("19:30:00");
        $this->assertEquals(new GermanDateTime("18:00:00"), $timeSpan->getStartTime($timeE));
        $this->assertEquals(new GermanDateTime("18:00:00"), $timeSpan->getEndTime($timeE));
    }

    /**
     * Checks that the default minimum and maxmimum times are used if none are configured.
     */
    public function testCanFallbackToDefaultLimits()
    {
        $relativeTimeSpanConfig = new ConfigParser(array(
            "past" => "3:40",
            "future" => "1:04"
        ));

        $timeSpan = new RelativeTimeSpan($relativeTimeSpanConfig);

        // Time in between start and end date
        $timeA = new GermanDateTime("16:00:00");
        $this->assertEquals(new GermanDateTime("12:20:00"), $timeSpan->getStartTime($timeA));
        $this->assertEquals(new GermanDateTime("17:04:00"), $timeSpan->getEndTime($timeA));

        // Time whose relative start time is below minimum
        $timeB = new GermanDateTime("02:30:00");
        $this->assertEquals(new GermanDateTime("00:00:00"), $timeSpan->getStartTime($timeB));
        $this->assertEquals(new GermanDateTime("03:34:00"), $timeSpan->getEndTime($timeB));

        // Time whose relative end time is above maximum
        $timeC = new GermanDateTime("23:20:00");
        $this->assertEquals(new GermanDateTime("19:40:00"), $timeSpan->getStartTime($timeC));
        $this->assertEquals(new GermanDateTime("23:59:00"), $timeSpan->getEndTime($timeC));
    }

    /**
     * Checks that the default past and future durations are used if none are configured.
     */
    public function testCanFallbackToDefaultDurations()
    {
        $relativeTimeSpanConfig = new ConfigParser(array(
            "min" => "12:00",
            "max" => "20:00"
        ));

        $timeSpan = new RelativeTimeSpan($relativeTimeSpanConfig);

        // Time in between start and end date
        $timeA = new GermanDateTime("14:01:00");
        $this->assertEquals(new GermanDateTime("14:01:00"), $timeSpan->getStartTime($timeA));
        $this->assertEquals(new GermanDateTime("14:01:00"), $timeSpan->getEndTime($timeA));

        // Time whose relative start time is below minimum
        $timeB = new GermanDateTime("10:45:00");
        $this->assertEquals(new GermanDateTime("12:00:00"), $timeSpan->getStartTime($timeB));
        $this->assertEquals(new GermanDateTime("12:00:00"), $timeSpan->getEndTime($timeB));

        // Time whose relative end time is above maximum
        $timeC = new GermanDateTime("21:55:00");
        $this->assertEquals(new GermanDateTime("20:00:00"), $timeSpan->getStartTime($timeC));
        $this->assertEquals(new GermanDateTime("20:00:00"), $timeSpan->getEndTime($timeC));
    }
}
