<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Util\DateIntervalConverter;

/**
 * Checks that the DateIntervalConverter class works as expected.
 */
class DateIntervalConverterTest extends TestCase
{
    /**
     * Checks that DateInterval's are converted to seconds as expected.
     */
    public function testCanConvertDateIntervalsToSeconds()
    {
        $dateInterval = new DateInterval("PT2H4M3S");
        $this->assertEquals(7443, DateIntervalConverter::toSeconds($dateInterval));

        $dateInterval = new DateInterval("PT0H10M0S");
        $this->assertEquals(600, DateIntervalConverter::toSeconds($dateInterval));
    }

    /**
     * Checks that DateInterval's are converted to minutes as expected.
     */
    public function testCanConvertDateIntervalsToMinutes()
    {
        $dateInterval = new DateInterval("PT2H4M3S");
        $this->assertEquals(124, DateIntervalConverter::toMinutes($dateInterval));

        $dateInterval = new DateInterval("PT0H10M35S");
        $this->assertEquals(11, DateIntervalConverter::toMinutes($dateInterval),"Minutes are rounded");
    }

}
