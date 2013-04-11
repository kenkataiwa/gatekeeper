<?php

namespace Gatekeeper\Provider\Model;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
abstract class GatekeeperModel {

    /**
     * the provider api client (optional) 
     */
    public $api = NULL;

    /**
     * Initialize Gatekeeper Provider
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