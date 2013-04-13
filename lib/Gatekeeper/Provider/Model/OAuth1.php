<?php

namespace Gatekeeper\Provider\Model;

use \Exception;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
class OAuth1 extends AbstractModel {

    /**
     * Request_tokens as recived from provider
     *
     * @var type
     */
	public $request_tokens_raw = null;

    /**
     * Access_tokens as received from provider
     *
     * @var type
     */
	public $access_tokens_raw  = null;

    /**
     *
     * @var type
     */
    private $http_status_codes = array(
        200 => "OK: Success!",
        304 => "Not Modified: There was no new data to return.",
        400 => "Bad Request: The request was invalid.",
        401 => "Unauthorized.",
        403 => "Forbidden: The request is understood, but it has been refused.",
        404 => "Not Found: The URI requested is invalid or the resource requested does not exists.",
        406 => "Not Acceptable.",
        500 => "Internal Server Error: Something is broken.",
        502 => "Bad Gateway.",
        503 => "Service Unavailable."
    );

    /**
     * Initialize OAuth Provider
     *
     * - Check the needed parameters ( stored in $this->params )
     * - Creating an instance of the api
     *
     * @return Object
     */
    protected function initialize() {

    }

    function loginBegin() {

    }

    function loginFinish() {

    }

    function refreshToken() {

    }

    function getUserProfile() {
        throw new Exception("Provider does not support this feature.", 8);
    }

}