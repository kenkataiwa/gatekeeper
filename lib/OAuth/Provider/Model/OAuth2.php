<?php

namespace OAuth\Provider\Model;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
class OAuthModel extends OAuthModel {

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

    function getUserProfile() {
        throw new Exception("Provider does not support this feature.", 8);
    }

}