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

/**
 * Checks that the ConfigParser class works as expected.
 */
class ConfigParserTest extends TestCase
{
    /**
     * Checks that the default value is returned if a config key is not set.
     */
    public function testCanReturnDefaultValueIfKeyIsNotSet()
    {
        $config = new ConfigParser(array("hallo" => "welt"));

        $this->assertNull($config->get("universum"));
        $this->assertNull($config->get("somevalue", null));
        $this->assertFalse($config->get("falsch", false));
        $this->assertTrue($config->get("Herunterfahren", true));
        $this->assertEquals("text", $config->get("Bildschirminhalt", "text"));
        $this->assertEquals(192387, $config->get("wichtigeZahl", 192387));
        $this->assertEquals(175.123, $config->get("nekommazahl", 175.123));
        $this->assertEquals(
            array("eine", "ganze", "liste", "von", "Werten", INF),
            $config->get("zeug", array("eine", "ganze", "liste", "von", "Werten", INF))
        );
        $this->assertEquals(
            (object)array("json" => 7), $config->get("someStdClassObject", (object)array("json" => 7))
        );
        $this->assertEquals(
            new ConfigParser(array("subconfig" => true)),
            $config->get("mysubconfig", new ConfigParser(array("subconfig" => true)))
        );
    }

    /**
     * Checks that the config value is returned if the config key is set.
     */
    public function testCanReturnConfigValueIfKeyIsSet()
    {
        $config = new ConfigParser(array(
            "text" => "Wortschlange",
            "Wahrheit" => true,
            "Unwahrheit" => false,
            "EineZahlenReihe" => 123456786543,
            "DeineKommaZahl" => 16543.123246,
            "MeineListe" => array("adaiuw", "sfj", 234, "adi"),
            "DasWichtigsteObjekt" => (object)array("read" => "write", "write" => "create", "create" => "read"),
            "derSchluss" => new ConfigParser(array("lies diese config ..." => "g"))
        ));

        $this->assertEquals("Wortschlange", $config->get("text"));
        $this->assertTrue($config->get("Wahrheit"));
        $this->assertFalse($config->get("Unwahrheit"));
        $this->assertEquals(123456786543, $config->get("EineZahlenReihe"));
        $this->assertEquals(16543.123246, $config->get("DeineKommaZahl"));
        $this->assertEquals(array("adaiuw", "sfj", 234, "adi"), $config->get("MeineListe"));
        $this->assertEquals(
            (object)array("read" => "write", "write" => "create", "create" => "read"),
            $config->get("DasWichtigsteObjekt")
        );
        $this->assertEquals(
            new ConfigParser(array("lies diese config ..." => "g")),
            $config->get("derSchluss")
        );
    }
}
