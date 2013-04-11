<?php

namespace Gatekeeper\Provider;

class Adapter {

    /**
     * Provider ID (or unique name)
     */
    private $id = NULL;

    /**
     * Provider adapter specific config
     */
    private $config = array();

    /**
     * Provider adapter extra parameters
     */
    private $params = NULL;

    /**
     * Provider adapter wrapper path
     */
    private $wrapper = NULL;

    /**
     * Provider adapter instance
     */
    private $adapter = NULL;

    /*
     * Set config
     */

    public function setConfig(array $config) {
        $this->config = $config;
    }

    /**
     * Create a new adapter switch IDp name or ID
     *
     * @param string  $id      The id or name of the IDp
     * @param array   $params  (optional) required parameters by the adapter
     */
    function factory($id, $params = null) {

# init the adapter config and params
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

# check the IDp adapter is enabled
        if (!$config["enabled"]) {
            throw new Exception("The provider '{$this->id}' is not enabled.", 3);
        }

# include the adapter wrapper
        if (isset($config["wrapper"]) && is_array($this->config["wrapper"])) {

            if (!class_exists($config["wrapper"]["class"])) {
                throw new Exception("Unable to load the adapter class.", 3);
            }

            $this->wrapper = $config["wrapper"]["class"];
        } else {

            $this->wrapper = 'Gatekeeper\Providers\\' . $this->id;
        }

# create the adapter instance, and pass the current params and config
        $this->adapter = new $this->wrapper($this->id, $this->config, $this->params);

        return $this;
    }

    /**
     * If the user is connected, then return the access_token and access_token_secret
     * if the provider api use gatekeeper
     */
    public function getAccessToken() {
        if (!$this->adapter->isUserConnected()) {
            Hybrid_Logger::error("User not connected to the provider.");

            throw new Exception("User not connected to the provider.", 7);
        }

        return
                ARRAY(
                    "access_token" => $this->adapter->token("access_token"), // Gatekeeper access token
                    "access_token_secret" => $this->adapter->token("access_token_secret"), // Gatekeeper access token secret
                    "refresh_token" => $this->adapter->token("refresh_token"), // Gatekeeper refresh token
                    "expires_in" => $this->adapter->token("expires_in"), // OPTIONAL. The duration in seconds of the access token lifetime
                    "expires_at" => $this->adapter->token("expires_at"), // OPTIONAL. Timestamp when the access_token expire. if not provided by the social api, then it should be calculated: expires_at = now + expires_in
        );
    }

    /**
     * return the provider config by id
     */
    function getConfigById($id) {
        if (isset($this->config["providers"][$id])) {
            return $this->config["providers"][$id];
        }

        return NULL;
    }

    /**
     * @return the provider config by id; insensitive
     */
    function getProviderCiId($id) {
        foreach ($this->config["providers"] as $idpid => $params) {
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
            return $this->adapter->$name($arguments[0]);
        } else {
            return $this->adapter->$name();
        }
    }

}