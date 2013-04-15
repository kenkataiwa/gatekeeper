<?php

namespace Gatekeeper\Provider\Model;

use \Exception,
    Gatekeeper\Auth;

/**
 * To implement an OAuth 2 based service provider, Gatekeeper\Provider\Model\OAuth2
 * can be used to save the hassle of the authentication flow.
 *
 * Each class that inherit from Gatekeeper\Provider\Model\OAuth2 have to implemenent
 * at least 2 methods:
 *   Gatekeeper\Services\{provider_name}->initialize()     to setup the provider api end-points urls
 *   Gatekeeper\Services\{provider_name}->getUserProfile() to grab the user profile
 *
 * Gatekeeper\Provider\Model\OAuth2 use OAuth2Client v0.1 which can be found on
 * Hybrid/thirdparty/OAuth/OAuth2Client.php
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
class OAuth2 extends AbstractModel {

    // default permissions
    protected $scope = "";
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
     * Try to get the error message from provider api
     */
    function errorMessageByStatus($code = null) {

        if (!$code && $this->api)
            $code = $this->api->http_code;

        if (isset($this->http_status_codes[$code]))
            return $code . " " . $this->http_status_codes[$code];
    }

    /**
     * Initialize OAuth Provider
     *
     * - Check the needed parameters ( stored in $this->params )
     * - Creating an instance of the api
     *
     * @return Object
     */
    protected function initialize() {
        if (!$this->config["keys"]["id"] || !$this->config["keys"]["secret"]) {
            throw new Exception("Your application id and secret are required in order to connect to {$this->providerId}.", 4);
        }

        // override requested scope
        if (isset($this->config["scope"]) && !empty($this->config["scope"])) {
            $this->scope = $this->config["scope"];
        }

        // create a new OAuth2 client instance
        $this->api = new OAuth2Client($this->config["keys"]["id"], $this->config["keys"]["secret"], $this->endpoint);

        // If we have an access token, set it
        if ($this->token("access_token")) {
            $this->api->access_token = $this->token("access_token");
            $this->api->refresh_token = $this->token("refresh_token");
            $this->api->access_token_expires_in = $this->token("expires_in");
            $this->api->access_token_expires_at = $this->token("expires_at");
        }

        // Set curl proxy if exist
        if (isset(Auth::$config["proxy"])) {
            $this->api->curl_proxy = Auth::$config["proxy"];
        }
    }

    /**
     * Begin login step
     */
    function loginBegin() {
        // redirect the user to the provider authentication url
        Auth::redirect($this->api->authorizeUrl(array("scope" => $this->scope)));
    }

    /**
     * Finish login step
     */
    function loginFinish() {
        $error = (array_key_exists('error', $_REQUEST)) ? $_REQUEST['error'] : "";

        // check for errors
        if ($error) {
            throw new Exception("Authentication failed! {$this->providerId} returned an error: $error", 5);
        }

        // try to authenicate user
        $code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : "";

        try {
            $this->api->authenticate($code);
        } catch (Exception $e) {
            throw new Exception("User profile request failed! {$this->providerId} returned an error: $e", 6);
        }

        // check if authenticated
        if (!$this->api->access_token) {
            throw new Exception("Authentication failed! {$this->providerId} returned an invalid access token.", 5);
        }

        // store tokens
        $this->token("access_token", $this->api->access_token);
        $this->token("refresh_token", $this->api->refresh_token);
        $this->token("expires_in", $this->api->access_token_expires_in);
        $this->token("expires_at", $this->api->access_token_expires_at);

        // set user connected locally
        $this->setUserConnected();
    }

    function refreshToken() {
        // have an access token?
        if ($this->api->access_token) {

            // have to refresh?
            if ($this->api->refresh_token && $this->api->access_token_expires_at) {

                // expired?
                if ($this->api->access_token_expires_at <= time()) {
                    $response = $this->api->refreshToken(array("refresh_token" => $this->api->refresh_token));

                    if (!isset($response->access_token) || !$response->access_token) {
                        // set the user as disconnected at this point and throw an exception
                        $this->setUserUnconnected();

                        throw new Exception("The Authorization Service has return an invalid response while requesting a new access token. " . (string) $response->error);
                    }

                    // set new access_token
                    $this->api->access_token = $response->access_token;

                    if (isset($response->refresh_token))
                        $this->api->refresh_token = $response->refresh_token;

                    if (isset($response->expires_in)) {
                        $this->api->access_token_expires_in = $response->expires_in;

                        // even given by some idp, we should calculate this
                        $this->api->access_token_expires_at = time() + $response->expires_in;
                    }
                }
            }

            // re store tokens
            $this->token("access_token", $this->api->access_token);
            $this->token("refresh_token", $this->api->refresh_token);
            $this->token("expires_in", $this->api->access_token_expires_in);
            $this->token("expires_at", $this->api->access_token_expires_at);
        }
    }

}