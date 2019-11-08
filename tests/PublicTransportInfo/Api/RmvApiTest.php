<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PublicTransportInfo\Api\Rmv\Request\ArrivalBoardRequest;
use PublicTransportInfo\Api\Rmv\Request\DepartureBoardRequest;
use PublicTransportInfo\Api\RmvApi;

/**
 * Checks that the RmvApi works as expected.
 */
class RmvApiTest extends TestCase
{
    /**
     * The http client mock
     * @var MockObject|\GuzzleHttp\Client $httpClientMock
     */
    private $httpClientMock;

    /**
     * The test RmvApi instance
     * @var RmvApi $rmvApi
     */
    private $rmvApi;


    /**
     * Method that is called before a test is executed.
     * Initializes the http client mock and the test RmvApi instance.
     */
    protected function setUp()
    {
        $this->httpClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
                                     ->setMethods(array("request"))
                                     ->getMock();
        $this->rmvApi = new RmvApi($this->httpClientMock, "secrettoken");
    }

    /**
     * Method that is called after a test was executed.
     * Unsets the http client mock and the test RmvApi instance.
     */
    protected function tearDown()
    {
        unset($this->httpClientMock);
        unset($this->rmvApi);
    }


    /**
     * Checks whether a ArrivalBoardRequest can be done as expected.
     */
    public function testCanDoArrivalBoardRequest()
    {
        $request = new ArrivalBoardRequest();
        $request->setExtId("123456789")
                ->setDuration(180)
                ->setRtMode(ArrivalBoardRequest::RT_MODE_OFF);


        $expectedMethod = "GET";
        $expectedUrl = "https://www.rmv.de/hapi/arrivalBoard";
        $expectedParameters = array(
            "query" => array(
                "accessId" => "secrettoken",
                "format" => "json",
                "extId" => "123456789",
                "duration" => 180,
                "rtMode" => "OFF"
            )
        );

        $responseMock = $this->getSuccessFulResponseMock(
            "{\"Arrival\":[], \"serverVersion\": \"1.9\", \"dialectVersion\": \"1.23\", \"requestId\": \"1570050431102\"}"
        );

        $this->httpClientMock->expects($this->once())
                             ->method("request")
                             ->with(
                                 $this->equalTo($expectedMethod),
                                 $this->equalto($expectedUrl),
                                 $this->equalTo($expectedParameters)
                             )
                             ->will($this->returnValue($responseMock));

        $responseJson = $this->rmvApi->doRequest($request);

        $this->assertEquals(array(), $responseJson["Arrival"]);
        $this->assertEquals("1.9", $responseJson["serverVersion"]);
        $this->assertEquals("1.23", $responseJson["dialectVersion"]);
        $this->assertEquals("1570050431102", $responseJson["requestId"]);
        $this->assertCount(4, $responseJson);
    }

    /**
     * Checks whether a unsuccessful request is handled as expected.
     */
    public function testCanHandleUnsuccessfulRequest()
    {
        $request = new DepartureBoardRequest();
        $request->setExtId("987654321")
            ->setDuration(30)
            ->setRtMode(DepartureBoardRequest::RT_MODE_INFOS);

        $expectedMethod = "GET";
        $expectedUrl = "https://www.rmv.de/hapi/departureBoard";
        $expectedParameters = array(
            "query" => array(
                "accessId" => "secrettoken",
                "format" => "json",
                "extId" => "987654321",
                "duration" => 30,
                "rtMode" => "INFOS"
            )
        );

        $responseMock = $this->getMockBuilder(Response::class)
                         ->setMethods(array("getStatusCode"))
                         ->getMock();
        $responseMock->expects($this->once())
                     ->method("getStatusCode")
                     ->will($this->returnValue(500));

        $this->httpClientMock->expects($this->once())
            ->method("request")
            ->with(
                $this->equalTo($expectedMethod),
                $this->equalto($expectedUrl),
                $this->equalTo($expectedParameters)
            )
            ->will($this->returnValue($responseMock));

        $this->expectExceptionMessage("Api Request was not successful");
        $this->rmvApi->doRequest($request);
    }

    /**
     * Returns a response mock for a successful response.
     *
     * @param string $_body The body to return by the `getBody()` method
     *
     * @return MockObject The response mock
     */
    private function getSuccessFulResponseMock($_body): MockObject
    {
        $responseMock = $this->getMockBuilder(Response::class)
                             ->setMethods(array("getStatusCode", "getBody"))
                             ->getMock();

        $responseMock->expects($this->once())
                     ->method("getStatusCode")
                     ->will($this->returnValue(200));

        $responseMock->expects($this->once())
                     ->method("getBody")
                     ->will($this->returnValue($_body));

        return $responseMock;
    }
}
