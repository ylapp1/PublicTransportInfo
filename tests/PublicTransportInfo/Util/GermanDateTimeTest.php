<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Util\GermanDateTime;

/**
 * Checks that the GermanDateTime class works as expected.
 */
class GermanDateTimeTest extends TestCase
{
    /**
     * Checks that a GermanDateTime can be constructed via the constructor as expected.
     */
    public function testCanCreateDateTimeViaConstructor()
    {
        $dateTime = new GermanDateTime("now");
        $this->assertEquals(new DateTimeZone("Europe/Berlin"), $dateTime->getTimezone());
    }

    /**
     * Checks that a GermanDateTime can be constructed via the "createFromFormat" method as expected.
     */
    public function testCanCreateDateTimeViaCreateFromFormat()
    {
        $dateTime = GermanDateTime::createFromFormat("H:i:s", "20:18:00");
        $this->assertEquals(new DateTimeZone("Europe/Berlin"), $dateTime->getTimezone());
    }
}
