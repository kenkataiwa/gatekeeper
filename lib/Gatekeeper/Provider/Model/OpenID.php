<?php

namespace Gatekeeper\Provider\Model;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
class OpenID extends AbstractModel {

	/**
     *  Openid provider identifier
     */
	public $openidIdentifier = "";

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

    function loginBegin() {

    }

    function loginFinish() {

    }

    function getUserProfile() {
        throw new Exception("Provider does not support this feature.", 8);
    }

}