<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

require_once __DIR__ . "/StationDepartureBoardTestBase.php";

use PublicTransportInfo\Api\Rmv\Request\DepartureBoardRequest;

/**
 * Checks that the DepartureBoardRequest works as expected.
 */
class DepartureBoardRequestTest extends StationDepartureBoardTestBase
{
    /**
     * ArrivalBoardRequestTest constructor.
     * Initializes the test class name.
     *
     * @param string $_name
     * @param array  $_data
     * @param string $_dataName
     */
    public function __construct($_name = null, array $_data = [], $_dataName = '')
    {
        parent::__construct($_name, $_data, $_dataName);
        $this->testClassName = DepartureBoardRequest::class;
    }


    /**
     * Checks that the DepartureBoardRequest returns the expected API path.
     */
    public function testCanReturnApiPath()
    {
        $request = new DepartureBoardRequest();
        $this->assertEquals("departureBoard", $request->getApiPath());
    }
}
