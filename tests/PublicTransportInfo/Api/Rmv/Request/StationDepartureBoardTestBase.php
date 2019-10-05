<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Api\Rmv\Request\StationBoardRequest;

/**
 * Base class for tests of StationDepartureBoard child classes.
 * Make sure to set the $testClassName attribute in the extending tests constructors.
 */
class StationDepartureBoardTestBase extends TestCase
{
    /**
     * The name of the class that will be instantiated for the tests.
     * @var string $testClassName
     */
    protected $testClassName;


    /**
     * Checks that the request parameters are generated as expected.
     * Only to the extent in which the class is used in the code.
     */
    public function testCanGenerateRequestParameters()
    {
        $request = $this->createTestClassInstance();

        // Only the methods that are really used in the code are checked here
        $request->setExtId("003011005")
                ->setRtMode(StationBoardRequest::RT_MODE_REALTIME)
                ->setStartDateTime(new DateTime("2019-10-02 15:00:00"))
                ->setEndDateTime(new DateTime("2019-10-02 17:00:00"))
                ->excludeLine("100")
                ->excludeLine("104");

        $requestParameters = $request->getRequestParameters();
        $this->assertEquals("003011005", $requestParameters["extId"]);
        $this->assertEquals("REALTIME", $requestParameters["rtMode"]);
        $this->assertEquals("2019-10-02", $requestParameters["date"]);
        $this->assertEquals("15:00", $requestParameters["time"]);
        $this->assertEquals(120, $requestParameters["duration"]);
        $this->assertEquals("!100,!104", $requestParameters["lines"]);
        $this->assertCount(6, $requestParameters);


        // Let's try a request with the minimum amount of parameters
        $request = $this->createTestClassInstance();
        $request->setExtId("003021243");
        $requestParameters = $request->getRequestParameters();

        $this->assertEquals("003021243", $requestParameters["extId"]);
        $this->assertCount(1, $requestParameters);
    }

    /**
     * Checks that the duration is auto adjusted to be between 0 and 1439.
     */
    public function testAutoAdjustDuration()
    {
        // Duration less than minimum allowed duration
        $request = $this->createTestClassInstance();
        $request->setExtId("abc123")
                ->setDuration(-500);

        $requestParameters = $request->getRequestParameters();
        $this->assertEquals(0, $requestParameters["duration"]);


        // Duration greater than maximum allowed duration
        $request = $this->createTestClassInstance();
        $request->setExtId("123abc")
                ->setDuration(99999);

        $requestParameters = $request->getRequestParameters();
        $this->assertEquals(1439, $requestParameters["duration"]);
    }


    /**
     * Checks that the validation detects that no id and no extId are set.
     */
    public function testDetectsIdAndExtIdNotSet()
    {
        $request = $this->createTestClassInstance();

        $this->expectExceptionMessage("Id or extId must be set");
        $request->validate();
    }

    /**
     * Checks that the validation detects invalid realtime modes.
     *
     * @param string $_invalidRtMode The invalid realtime mode
     *
     * @dataProvider detectsInvalidRtModeDataProvider()
     */
    public function testDetectsInvalidRtMode(string $_invalidRtMode)
    {
        $request = $this->createTestClassInstance();
        $request->setExtId("bla")
                ->setRtMode($_invalidRtMode);

        $this->expectExceptionMessage("Specified realtime mode \"" . $_invalidRtMode . "\" is invalid");
        $request->validate();
    }

    /**
     * Returns data sets for the testDetectsInvalidRtMode() test.
     * @return array The data sets
     */
    public function detectsInvalidRtModeDataProvider(): array
    {
        return array(
            array("boardDeparture"),
            array("departurearrival"),
            array("arrivalDeparture")
        );
    }


    /**
     * Creates and returns an instance of the test class.
     * @return StationBoardRequest The test class instance
     */
    private function createTestClassInstance(): StationBoardRequest
    {
        return new $this->testClassName();
    }
}
