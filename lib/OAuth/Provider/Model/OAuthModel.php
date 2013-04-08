<?php

namespace OAuth\Provider\Model;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
abstract class OAuthModel {

    /**
     * the provider api client (optional) 
     */
    public $api = NULL;

    /**
     * Initialize OAuth Provider
     *
     * - Check the needed parameters ( stored in $this->params )
     * - Creating an instance of the api
     *
     * @return Object
     */
    abstract protected function initialize();

    function getUserProfile() {
        throw new Exception("Provider does not support this feature.", 8);
    }

}