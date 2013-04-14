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
        $this->initialize($config);
    }

    public function initialize($config) {
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
        if (!function_exists('curl_init')) {
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
        $provider->setStorage($this->getStorage());
        $provider->factory($providerId, $params);
        return $provider;
    }

    public function getConfig() {
        return $this->config;
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

    /**
     * Utility function, redirect to a given URL with php header or using javascript location.href
     */
    public static function redirect($url, $mode = "PHP") {

        if ($mode == "PHP") {
            header("Location: $url");
        } elseif ($mode == "JS") {
            echo '<html>';
            echo '<head>';
            echo '<script type="text/javascript">';
            echo 'function redirect(){ window.top.location.href="' . $url . '"; }';
            echo '</script>';
            echo '</head>';
            echo '<body onload="redirect()">';
            echo 'Redirecting, please wait...';
            echo '</body>';
            echo '</html>';
        }

        die();
    }

    /**
     * Utility function, return the current url.
     *
     * @param bool $request_uri TRUE to get $_SERVER['REQUEST_URI'], FALSE for $_SERVER['PHP_SELF']
     */
    public static function getCurrentUrl($request_uri = true) {
        if (
                isset($_SERVER['HTTPS']) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        $url = $protocol . $_SERVER['HTTP_HOST'];

        // use port if non default
        if (isset($_SERVER['SERVER_PORT']) && strpos($url, ':' . $_SERVER['SERVER_PORT']) === FALSE) {
            $url .= ($protocol === 'http://' && $_SERVER['SERVER_PORT'] != 80 && !isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) || ($protocol === 'https://' && $_SERVER['SERVER_PORT'] != 443 && !isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? ':' . $_SERVER['SERVER_PORT'] : '';
        }

        if ($request_uri) {
            $url .= $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['PHP_SELF'];
        }

        // return current url
        return $url;
    }

}

?>
