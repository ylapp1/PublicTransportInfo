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
 * The DepartureBoard API request.
 *
 * @see https://www.rmv.de/hapi/departureBoard?wadl
 */
class DepartureBoardRequest extends StationBoardRequest
{
    public function getApiPath(): string
    {
        return "departureBoard";
    }
}
