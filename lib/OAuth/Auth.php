<?php

namespace OAuth;

use OAuth\Provider\Adapter;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Auth {

    private $version = "1.0.0-dev";
    private $config = array();

    function __construct(array $config) {

        $this->config = $config;
    }

    /**
     * @var OAuth\Provider
     */
    private $provider;

    /**
     * Try to authenticate the user with a given provider.
     *
     * If the user is already connected we just return and instance of provider adapter,
     * ELSE, try to authenticate and authorize the user with the provider.
     *
     * @params is generally an array with required info in order for this provider and OAuth to work
     */
    public function authenticate($providerId, array $params = NULL) {

        $loggedIn = false;
        if ($loggedIn) {
            // Loggin
        } else {
            $this->setProvider($this->setup($providerId, $params));
            return $this->provider;
        }
    }

    /**
     * Setup an adapter for a given provider
     */
    public function setup($providerId, array $params = NULL) {

        // instantiate a new IDProvider Adapter
        $provider = new Adapter;
        $provider->setConfig($this->config);
        $provider->factory($providerId, $params);
//        print_r($provider);
        return $provider;
    }

    /**
     * Set providers
     */
    public function setProvider(Adapter $provider) {
        $this->provider = $provider;
    }

    /**
     * Get provider
     */
    public function getProvider() {
        return $this->provider;
    }

}

?>
