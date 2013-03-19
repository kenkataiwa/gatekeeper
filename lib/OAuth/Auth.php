<?php

namespace OAuth;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Auth {

    function __construct(array $config) {

    }

    /**
     * Try to authenticate the user with a given provider.
     *
     * If the user is already connected we just return and instance of provider adapter,
     * ELSE, try to authenticate and authorize the user with the provider.
     *
     * $params is generally an array with required info in order for this provider and OAuth to work
     */
    public static function authenticate($providerId, array $params = NULL) {

    }

}

?>
