<?php

namespace Gatekeeper;

use Gatekeeper\Provider\Adapter,
    Gatekeeper\Storage;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Auth {

    public static $version = "0.1.0-dev";

    private $config = array();

    /**
     * @var Gatekeeper\Storage
     */
    private $storage;

    /**
     * @var Gatekeeper\Provider
     */
    private $provider;

    public function __construct(array $config) {
        $this->initializa($config);
    }

    public function initializa($config) {
        if (!is_array($config) && !file_exists($config)) {
            throw new Exception("Gatekeeper config does not exist on the given path.", 1);
        }

        if (!is_array($config)) {
            $this->config = include $config;
        } else {
            $this->config = $config;
        }

        $this->storage = new Storage;

		// PHP Curl extension [http://www.php.net/manual/en/intro.curl.php]
		if ( ! function_exists('curl_init') ) {
			throw new Exception('Gatekeeper Library needs the CURL PHP extension.');
		}
        
        return $this;
    }

    /**
     * Try to authenticate the user with a given provider.
     *
     * If the user is already connected we just return and instance of provider adapter,
     * ELSE, try to authenticate and authorize the user with the provider.
     *
     * @params is generally an array with required info in order for this provider and Gatekeeper to work
     */
    public function authenticate($providerId, array $params = NULL) {

        if (!$this->getStorage()->get("gatekeeper_session.$providerId.is_logged_in")) {
            $this->setProvider($this->setup($providerId, $params));
            $this->provider->login();
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

    /**
     * Set provider
     */
    public function setStorage(Storage $storage) {
        $this->storage = $storage;
    }

    /**
     * Get provider
     */
    public function getStorage() {
        return $this->storage;
    }

}

?>
