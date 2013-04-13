<?php

namespace Gatekeeper\Provider;

use \Exception;

class Adapter {

    /**
     * Provider ID (or unique name)
     */
    private $id = NULL;

    /**
     * Service provider specific config
     */
    private $config = array();

    /**
     * Service provider extra parameters
     */
    private $params = NULL;

    /**
     * Service provider wrapper path
     */
    private $wrapper = NULL;

    /**
     * Service Provider instance
     */
    private $service = NULL;

    /**
     * Set config
     */
    public function setConfig(array $config) {
        $this->config = $config;
    }

    /**
     * Create a new service switch IDp name or ID
     *
     * @param string  $id      The id or name of the IDp
     * @param array   $params  (optional) required parameters by the service
     */
    function factory($id, $params = null) {

        # init the service config and params
        $this->id = $id;
        $this->params = $params;
        $this->id = $this->getProviderCiId($this->id);
        $config = $this->getConfigById($this->id);

        # check the IDp id
        if (!$this->id) {
            throw new Exception("No provider ID specified.", 2);
        }

        # check the IDp config
        if (!$config) {
            throw new Exception("Unknown Provider ID, check your configuration file.", 3);
        }

        # check the IDp service is enabled
        if (!$config["enabled"]) {
            throw new Exception("The provider '{$this->id}' is not enabled.", 3);
        }

        # include the service wrapper
        if (isset($config["wrapper"]) && is_array($this->config["wrapper"])) {

            if (!class_exists($config["wrapper"]["class"])) {
                throw new Exception("Unable to load the service class.", 3);
            }

            $this->wrapper = $config["wrapper"]["class"];
        } else {

            $this->wrapper = 'Gatekeeper\Services\\' . $this->id;
        }

        # create the service instance, and pass the current params and config
        $this->service = new $this->wrapper($this->id, $this->config, $this->params);

        # Redirect or load Endpoint

    }

    /**
     * Prepare the user session and the authentication request
     */
    public function login() {

        if (!$this->service) {
            throw new Exception("Gatekeeper\Provider\Adapter->login() should not directly used.");
        }

		// Make a fresh start
		$this->logout();

        // Finally redirect to log in url
    }

    /**
     * Let Gatekeeper forget all about the user for the current provider
     */
    public function logout() {

    }

    /**
     * Return true if the user is connected to the current provider
     */
    public function isUserConnected() {

    }

    /**
     * If the user is connected, then return the access_token and access_token_secret
     * if the provider api use gatekeeper
     */
    public function getAccessToken() {
        if (!$this->service->isUserConnected()) {
            Hybrid_Logger::error("User not connected to the provider.");

            throw new Exception("User not connected to the provider.", 7);
        }

        return array(
            "access_token" => $this->service->token("access_token"), // Gatekeeper access token
            "access_token_secret" => $this->service->token("access_token_secret"), // Gatekeeper access token secret
            "refresh_token" => $this->service->token("refresh_token"), // Gatekeeper refresh token
            "expires_in" => $this->service->token("expires_in"), // OPTIONAL. The duration in seconds of the access token lifetime
            "expires_at" => $this->service->token("expires_at"), // OPTIONAL. Timestamp when the access_token expire. if not provided by the social api, then it should be calculated: expires_at = now + expires_in
        );
    }

    /**
     * Naive getter of the current connected IDp API client
     */
    function api() {
        if (!$this->adapter->isUserConnected()) {
            throw new Exception("User not connected to the provider.", 7);
        }
        return $this->adapter->api;
    }

    /**
     * redirect the user to hauth_return_to (the callback url)
     */
    function returnToCallbackUrl() {

    }

    /**
     * return the provider config by id
     */
    function getConfigById($id) {
        if (isset($this->config["services"][$id])) {
            return $this->config["services"][$id];
        }

        return NULL;
    }

    /**
     * @return the provider config by id; insensitive
     */
    function getProviderCiId($id) {
        foreach ($this->config["services"] as $idpid => $params) {
            if (strtolower($idpid) == strtolower($id)) {
                return $idpid;
            }
        }

        return NULL;
    }

    /**
     * Handle
     *   getUserProfile()
     *   getUserContacts()
     *   getUserActivity()
     *   setUserStatus()
     */
    public function __call($name, $arguments) {

        if (count($arguments)) {
            return $this->service->$name($arguments[0]);
        } else {
            return $this->service->$name();
        }
    }

}