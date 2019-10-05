<?php
/**
 * @file
 * @version 0.1
 * @copyright 2019 Yannick Lapp
 * @author Yannick Lapp <yannick.lapp@cn-consult.eu
 * @license MIT
 */

namespace PublicTransportInfo\Api\Rmv\Request;

/**
 * Base class for RMV API requests.
 */
abstract class Request
{
    /**
     * Returns the path of the RMV API to use (e.g. "arrivalBoard", "departureBoard", etc.)
     * @return string The API path
     */
    abstract public function getApiPath(): string;

    /**
     * Validates this Request.
     * Throws exceptions if this Request is not valid.
     */
    abstract public function validate();

    /**
     * Generates and returns the HTTP request parameters as an associative array from this Request's configuration.
     * @return mixed[] The request parameters
     */
    abstract public function getRequestParameters(): array;
}
