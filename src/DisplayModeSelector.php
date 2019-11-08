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
use PublicTransportInfo\Util\ConfigParser;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Selects the display mode based on a specified time.
 */
class DisplayModeSelector
{
    // The available display modes
    const DISPLAY_NONE = 1;
    const DISPLAY_ARRIVAL = 2;
    const DISPLAY_DEPARTURE = 3;


    /**
     * The start time
     * Before this time the display mode will be DISPLAY_NONE
     * Equal or after this time and before the mode switch time the display mode will be DISPLAY_ARRIVAL
     *
     * @var DateTime $startTime
     */
    private $startTime;

    /**
     * The mode switch time
     * Equal or after this time and before the end time the display mode will be DISPLAY_DEPARTURE
     *
     * @var DateTime $modeSwitchTime
     */
    private $modeSwitchTime;

    /**
     * The end time
     * After this time DISPLAY_NONE will be the selected display mode
     *
     * @var DateTime $endTime
     */
    private $endTime;


    /**
     * DisplayModeSelector constructor.
     *
     * @param ConfigParser $_config The config
     */
    public function __construct(ConfigParser $_config)
    {
        $this->configure($_config);
    }


    /**
     * Configures this DisplayModeSelector.
     *
     * @param ConfigParser $_config The config
     */
    private function configure(ConfigParser $_config)
    {
        $this->startTime = GermanDateTime::createFromFormat("H:i", $_config->get("start", "0:00"));
        $this->endTime = GermanDateTime::createFromFormat("H:i", $_config->get("end", "24:00"));
        $this->modeSwitchTime = GermanDateTime::createFromFormat("H:i", $_config->get("modeSwitch", "12:00"));
    }

    /**
     * Returns the display mode for a specified time.
     *
     * @param DateTime $_time The time to fetch the display mode for
     *
     * @return int The display mode (one of the DISPLAY_* constants)
     */
    public function getDisplayMode(DateTime $_time): int
    {
        if ($_time < $this->startTime || $_time > $this->endTime)
        {
            return DisplayModeSelector::DISPLAY_NONE;
        }
        elseif ($_time < $this->modeSwitchTime) return DisplayModeSelector::DISPLAY_ARRIVAL;
        else return DisplayModeSelector::DISPLAY_DEPARTURE;
    }
}
