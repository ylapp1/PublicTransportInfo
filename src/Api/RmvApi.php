<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Api;

use Exception;
use GuzzleHttp\Client;
use PublicTransportInfo\Api\Rmv\Request\Request;

/**
* The API to retrieve data from rmv.de.
*
* @see https://opendata.rmv.de/site/start.html
*/
class RmvApi
{
	/**
	 * The API token which must be sent with every request
	 * @var string $apiToken
	 */
	private $apiToken;

    /**
     * The http client that is used to send http requests to the RMV server
     * @var Client $httpClient
     */
	private $httpClient;

    /**
     * The base URL of the API server
     * @var string $baseUrl
     */
	private $baseUrl = "https://www.rmv.de";

    /**
     * The api base path
     * @var string $apiBasePath
     */
	private $apiBasePath = "hapi";


    /**
     * RmvApi constructor.
     *
     * @param Client $_httpClient The http client to use to do API requests
     * @param string $_apiToken The API token that must be sent with every API request
     */
	public function __construct(Client $_httpClient, string $_apiToken)
	{
		$this->apiToken = $_apiToken;
		$this->httpClient = $_httpClient;
	}


    /**
     * Sends a request to the RMV api and returns the result as json decoded object.
     *
     * @param Request $_request The API request to send to the server
     *
     * @return array The decoded json response
     *
     * @throws Exception The exception when the response status code is not 200
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
	public function doRequest(Request $_request): array
    {
        $url = $this->baseUrl . "/" . $this->apiBasePath . "/" . $_request->getApiPath();

        $defaultRequestOptions = array("accessId" => $this->apiToken, "format" => "json");
        $requestOptions = array("query" => array_merge(
            $_request->getRequestParameters(), $defaultRequestOptions
        ));

        $response = $this->httpClient->request("GET", $url, $requestOptions);
        if ($response->getStatusCode() == 200)
        {
            return json_decode($response->getBody(), true);
        }
        else throw new Exception("Api Request was not successful");
    }
}
