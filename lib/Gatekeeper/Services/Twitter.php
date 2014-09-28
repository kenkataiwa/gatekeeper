<?php

namespace Gatekeeper\Services;

/**
 * Twitter
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
use \Exception;
use Gatekeeper\Auth;
use Gatekeeper\User\Activity;
use Gatekeeper\User\Contact;
use Gatekeeper\Provider\Model\OAuth1;

class Twitter extends OAuth1 {

    /**
     * IDp wrappers initializer
     */
    function initialize() {
        parent::initialize();

        // Provider api end-points
        $this->api->api_base_url = "https://api.twitter.com/1.1/";
        $this->api->authorize_url = "https://api.twitter.com/oauth/authenticate";
        $this->api->request_token_url = "https://api.twitter.com/oauth/request_token";
        $this->api->access_token_url = "https://api.twitter.com/oauth/access_token";

        if (isset($this->config['api_version']) && $this->config['api_version']) {
            $this->api->api_base_url = "https://api.twitter.com/{$this->config['api_version']}/";
        }

        if (isset($this->config['authorize']) && $this->config['authorize']) {
            $this->api->authorize_url = "https://api.twitter.com/oauth/authorize";
        }

        $this->api->curl_auth_header = false;
    }

    /**
     * begin login step
     */
    function loginBegin() {
        $tokens = $this->api->requestToken($this->endpoint);

        // request tokens as recived from provider
        $this->request_tokens_raw = $tokens;

        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("Authentification failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code), 5);
        }

        if (!isset($tokens["oauth_token"])) {
            throw new Exception("Authentification failed! {$this->providerId} returned an invalid oauth token.", 5);
        }

        $this->token("request_token", $tokens["oauth_token"]);
        $this->token("request_token_secret", $tokens["oauth_token_secret"]);

        // redirect the user to the provider authentication url with force_login
        if (isset($this->config['force_login']) && $this->config['force_login']) {
            Auth::redirect($this->api->authorizeUrl($tokens, array('force_login' => true)));
        }

        // else, redirect the user to the provider authentication url
        Auth::redirect($this->api->authorizeUrl($tokens));
    }

    /**
     * load the user profile from the IDp api client
     */
    function getUserProfile() {
        $response = $this->api->get('account/verify_credentials.json');

        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("User profile request failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code), 6);
        }

        if (!is_object($response) || !isset($response->id)) {
            throw new Exception("User profile request failed! {$this->providerId} api returned an invalid response.", 6);
        }

        // Exploding name to first name and last name
        $name = (property_exists($response, 'name')) ? $response->name : null;
        $names = explode(' ', $name);
        // Store the user profile.
        $this->user->profile->identifier = (property_exists($response, 'id')) ? $response->id : null;
        $this->user->profile->displayName = (property_exists($response, 'name')) ? $response->name : null;
        $this->user->profile->description = (property_exists($response, 'description')) ? $response->description : null;
        $this->user->profile->firstName = isset($names[0]) ? $names[0] : null;
        $this->user->profile->lastName = isset($names[1]) ? $names[1] : null;
        $this->user->profile->username = (property_exists($response, 'screen_name')) ? $response->screen_name : null;
        $this->user->profile->photoURL = (property_exists($response, 'profile_image_url')) ? $response->profile_image_url : null;
        $this->user->profile->largePhoto = (property_exists($response, 'profile_image_url')) ? str_replace("_normal", '', $response->profile_image_url) : null;
        $this->user->profile->profileURL = (property_exists($response, 'screen_name')) ? ("http://twitter.com/" . $response->screen_name) : null;
        $this->user->profile->webSiteURL = (property_exists($response, 'url')) ? $response->url : null;
        $this->user->profile->region = (property_exists($response, 'location')) ? $response->location : null;

        return $this->user->profile;
    }

    /**
     * load the user contacts
     */
    function getUserContacts() {
        $parameters = array('cursor' => '-1');
        $response = $this->api->get('friends/ids.json', $parameters);

        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("User contacts request failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
        }

        if (!$response || !count($response->ids)) {
            return array();
        }

        // 75 id per time should be okey
        $contactsids = array_chunk($response->ids, 75);

        $contacts = array();

        foreach ($contactsids as $chunk) {
            $parameters = array('user_id' => implode(",", $chunk));
            $response = $this->api->get('users/lookup.json', $parameters);

            // check the last HTTP status code returned
            if ($this->api->http_code != 200) {
                throw new Exception("User contacts request failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
            }

            if ($response && count($response)) {
                foreach ($response as $item) {
                    $uc = new Contact();

                    $uc->identifier = (property_exists($item, 'id')) ? $item->id : null;
                    $uc->displayName = (property_exists($item, 'name')) ? $item->name : null;
                    $uc->profileURL = (property_exists($item, 'screen_name')) ? ("http://twitter.com/" . $item->screen_name) : null;
                    $uc->photoURL = (property_exists($item, 'profile_image_url')) ? $item->profile_image_url : null;
                    $uc->description = (property_exists($item, 'description')) ? $item->description : null;

                    $contacts[] = $uc;
                }
            }
        }

        return $contacts;
    }

    /**
     * update user status
     */
    function setUserStatus($status) {
        $parameters = array('status' => $status);
        $response = $this->api->post('statuses/update.json', $parameters);

        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("Update user status failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
        }
    }

    /**
     * load the user latest activity
     *    - timeline : all the stream
     *    - me       : the user activity only
     *
     * by default return the timeline
     */
    function getUserActivity($stream) {
        if ($stream == "me") {
            $response = $this->api->get('statuses/user_timeline.json');
        } else {
            $response = $this->api->get('statuses/home_timeline.json');
        }

        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("User activity stream request failed! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
        }

        if (!$response) {
            return array();
        }

        $activities = array();

        foreach ($response as $item) {
            $ua = new Activity();

            $ua->id = (property_exists($item, 'id')) ? $item->id : null;
            $ua->date = (property_exists($item, 'created_at')) ? strtotime($item->created_at) : null;
            $ua->text = (property_exists($item, 'text')) ? $item->text : null;

            $ua->user->identifier = (property_exists($item->user, 'id')) ? $item->user->id : null;
            $ua->user->displayName = (property_exists($item->user, 'name')) ? $item->user->name : null;
            $ua->user->profileURL = (property_exists($item->user, 'screen_name')) ? ("http://twitter.com/" . $item->user->screen_name) : null;
            $ua->user->photoURL = (property_exists($item->user, 'profile_image_url')) ? $item->user->profile_image_url : null;

            $activities[] = $ua;
        }

        return $activities;
    }
    
     
    /**
     * load the user's followers count    
     */
    function getFollowersCount($screen_name) {
        $response = $this->api->get('users/show.json?screen_name='.$screen_name);
      
        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("Followers count failed to load! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
        }
        if (!$response) {
            return array();
        }
        $followers = $response;
        return $followers;
    }


}
