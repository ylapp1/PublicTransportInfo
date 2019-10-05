<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\DisplayModeSelector;
use PublicTransportInfo\Util\ConfigParser;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Checks that the DisplayModeSelector class works as expected.
 */
class DisplayModeSelectorTest extends TestCase
{
    /**
     * Checks that the expected display mode is selected based on a config and a specified time.
     */
    public function testCanSelectDisplayMode()
    {
        $config = array(
            "start" => "07:00",
            "modeSwitch" => "11:00",
            "end" => "18:00"
        );
        $selector = new DisplayModeSelector(new ConfigParser($config));

        // Some test times
        $timeBeforeStart = new GermanDateTime("06:35:00");
        $timeJustBeforeStart = new GermanDateTime("06:59:59.999");
        $timeExactlyAtStart = new GermanDateTime("07:00:00");
        $timeBetweenStartAndModeSwitch = new GermanDateTime("09:20:00");
        $timeJustBeforeModeSwitch = new GermanDateTime("10:59:59.999");
        $timeExactlyAtModeSwitch = new GermanDateTime("11:00:00");
        $timeBetweenModeSwitchAndEnd = new GermanDateTime("14:45:00");
        $timeExactlyAtEnd = new GermanDateTime("18:00:00");
        $timeJustAfterEnd = new GermanDateTime("18:00:00.001");
        $timeAfterEnd = new GermanDateTime("19:15:00");

        $this->assertEquals(DisplayModeSelector::DISPLAY_NONE, $selector->getDisplayMode($timeBeforeStart));
        $this->assertEquals(DisplayModeSelector::DISPLAY_NONE, $selector->getDisplayMode($timeJustBeforeStart));
        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeExactlyAtStart));
        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeBetweenStartAndModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeJustBeforeModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeExactlyAtModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeBetweenModeSwitchAndEnd));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeExactlyAtEnd));
        $this->assertEquals(DisplayModeSelector::DISPLAY_NONE, $selector->getDisplayMode($timeJustAfterEnd));
        $this->assertEquals(DisplayModeSelector::DISPLAY_NONE, $selector->getDisplayMode($timeAfterEnd));
    }

    /**
     * Checks that the DisplayModeSelector can fall back to the default values if no values are specified.
     */
    public function testCanFallBackToDefaultValues()
    {
        $selector = new DisplayModeSelector(new ConfigParser(array()));

        // Some test times
        $timeExactlyAtStart = new GermanDateTime("00:00:00");
        $timeBetweenStartAndModeSwitch =  new GermanDateTime("05:34:00");
        $timeJustBeforeModeSwitch = new GermanDateTime("11:59:59.999");
        $timeExactlyAtModeSwitch = new GermanDateTime("12:00:00");
        $timeBetweenModeSwitchAndEnd = new GermanDateTime("19:37:00");
        $timeExactlyAtEnd = new GermanDateTime("24:00:00");

        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeExactlyAtStart));
        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeBetweenStartAndModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_ARRIVAL, $selector->getDisplayMode($timeJustBeforeModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeExactlyAtModeSwitch));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeBetweenModeSwitchAndEnd));
        $this->assertEquals(DisplayModeSelector::DISPLAY_DEPARTURE, $selector->getDisplayMode($timeExactlyAtEnd));
    }
}
