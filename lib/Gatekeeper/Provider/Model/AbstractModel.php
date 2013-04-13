<?php

namespace Gatekeeper\Provider\Model;

use \Exception;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
abstract class AbstractModel {

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