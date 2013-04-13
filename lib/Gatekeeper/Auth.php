<?php

namespace Gatekeeper;

use Gatekeeper\Provider\Adapter;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Auth {

    private $version = "0.1.0-dev";
    private $config = array();

    function __construct(array $config) {

        $this->config = $config;
    }

    /**
     * @var Gatekeeper\Provider
     */
    private $provider;

    /**
     * Try to authenticate the user with a given provider.
     *
     * If the user is already connected we just return and instance of provider adapter,
     * ELSE, try to authenticate and authorize the user with the provider.
     *
     * @params is generally an array with required info in order for this provider and Gatekeeper to work
     */
    public function authenticate($providerId, array $params = NULL) {

        $loggedIn = true;
        if ($loggedIn) {
            $this->setProvider($this->setup($providerId, $params));
            $this->provider->login();
            // Log in
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
        return $provider;
    }

    /**
     * Set provider
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
