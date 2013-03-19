<?php

namespace OAuth\Provider;

/**
 * Facebook
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
use OAuth\Provider\Model\OAuthModel;

class Facebook extends OAuthModel {

    private $scope = "email, user_about_me, user_birthday, user_hometown, user_website, read_stream, offline_access, publish_stream, read_friendlists";

    public function initialize() {
        ;
    }
}

