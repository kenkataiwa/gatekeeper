<?php

namespace Gatekeeper;

/**
 * The Gatekeeper\User class represents the current loggedin user
 *
 * @author kenkataiwa
 */
use Gatekeeper\User\Profile;

class User {

    /**
     * The ID (name) of the connected provider
     */
    public $providerId = NULL;

    /**
     *  timestamp connection to the provider
     */
    public $timestamp = NULL;

    /**
     * User profile, containts the list of fields available in the normalized user profile structure used by HybridAuth.
     */
    public $profile = NULL;

    function __construct() {
        $this->timestamp = time();
        $this->profile = new Profile();
    }

}

?>
