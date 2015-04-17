<?php

namespace Gatekeeper\User;

/**
 * Description of User
 *
 * @author kenkataiwa
 */
class Contact {

    /**
     * The Unique contact user ID
     */
    public $identifier = NULL;

    /**
     *  User website, blog, web page
     */
    public $webSiteURL = NULL;

    /**
     *  URL link to profile page on the IDp web site
     */
    public $profileURL = NULL;

    /**
     *  URL link to user photo or avatar
     */
    public $photoURL = NULL;

    /**
     *  User dispalyName provided by the IDp or a concatenation of first and last name
     */
    public $displayName = NULL;

    /**
     *  A short about_me
     */
    public $description = NULL;

    /**
     *  User email. Not all of IDp grant access to the user email
     */
    public $email = NULL;

    /**
     *  User phone. Not all of IDp grant access to the user email
     */
    public $phone = NULL;

}

?>
