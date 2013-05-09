<?php

namespace Gatekeeper\User;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Activity {

    /**
     * activity id on the provider side, usually given as integer
     */
    public $id = NULL;

    /**
     *  activity date of creation
     */
    public $date = NULL;

    /**
     *  activity content as a string
     */
    public $text = NULL;

    /**
     *  user who created the activity
     */
    public $user = NULL;

    public function __construct() {
        $this->user = new stdClass();

        // typically, we should have a few information about the user who created the event from social apis
        $this->user->identifier = NULL;
        $this->user->displayName = NULL;
        $this->user->profileURL = NULL;
        $this->user->photoURL = NULL;
    }

}

?>
