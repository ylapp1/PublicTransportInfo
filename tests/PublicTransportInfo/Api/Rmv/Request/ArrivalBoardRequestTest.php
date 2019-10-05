<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

require_once __DIR__ . "/StationDepartureBoardTestBase.php";

use PublicTransportInfo\Api\Rmv\Request\ArrivalBoardRequest;

/**
 * Checks that the ArrivalBoardRequest works as expected.
 */
class ArrivalBoardRequestTest extends StationDepartureBoardTestBase
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
        $this->testClassName = ArrivalBoardRequest::class;
    }


    /**
     * Checks that the ArrivalBoardRequest returns the expected API path.
     */
    public function testCanReturnApiPath()
    {
        $request = new ArrivalBoardRequest();
        $this->assertEquals("arrivalBoard", $request->getApiPath());
    }
}
