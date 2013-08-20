<?php

namespace Gatekeeper\User;

/**
 * Gatekeeper\User\Profile object represents the current logged in user profile.
 * The list of fields available in the normalized user profile structure used by Gatekeeper.
 *
 * The Gatekeeper\User\Profile object is populated with as much information about the user as
 * Gatekeeper was able to pull from the given API or authentication provider.
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
class Profile {

    /**
     * The Unique user's ID on the connected provider
     */
    public $identifier = NULL;

    /**
     * User website, blog, web page
     */
    public $webSiteURL = NULL;

    /**
     * URL link to profile page on the IDp web site
     */
    public $profileURL = NULL;

    /**
     * URL link to user photo or avatar
     */
    public $photoURL = NULL;

    /**
     * URL link to profile page on the IDp web site
     */
    public $largePhoto = NULL;

    /**
     * User dispalyName provided by the IDp or a concatenation of first and last name.
     */
    public $displayName = NULL;

    /**
     * A short about_me
     */
    public $description = NULL;

    /**
     * User's first name
     */
    public $firstName = NULL;

    /**
     * User's last name
     */
    public $lastName = NULL;

    /**
     * User's username
     */
    public $username = NULL;

    /**
     * male or female
     */
    public $gender = NULL;

    /**
     * language
     */
    public $language = NULL;

    /**
     * User age, we dont calculate it. we return it as is if the IDp provide it.
     */
    public $age = NULL;

    /**
     * User birth Day
     */
    public $birthDay = NULL;

    /**
     * User birth Month
     */
    public $birthMonth = NULL;

    /**
     *  User birth Year
     */
    public $birthYear = NULL;

    /**
     * User email. Note: not all of IDp garant access to the user email
     */
    public $email = NULL;

    /**
     * Verified user email. Note: not all of IDp garant access to verified user email
     */
    public $emailVerified = NULL;

    /**
     * phone number
     */
    public $phone = NULL;

    /**
     *  complete user address
     */
    public $address = NULL;

    /**
     * user country
     */
    public $country = NULL;

    /**
     * region
     */
    public $region = NULL;

    /**
     * City
     */
    public $city = NULL;

    /**
     * Postal code
     */
    public $zip = NULL;

}

?>
