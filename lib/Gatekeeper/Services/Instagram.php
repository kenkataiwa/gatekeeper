<?php

namespace Gatekeeper\Services;

/**
 * Instagram
 *
 * @author Jeremiah Mannyanda <jemanyanda02@hotmail.com>
 */
use \Exception;
use Gatekeeper\Auth;
use Gatekeeper\User\Activity;
use Gatekeeper\User\Contact;
use Gatekeeper\Provider\Model\OAuth1;

class Instagram extends OAuth1 {

    /**
     * IDp wrappers initializer
     */
    function initialize() {
        parent::initialize();

        // Provider api end-points
        $this->api->api_base_url = "https://api.instagram.com/v1";
        $this->api->authorize_url = "https://api.instagram.com/oauth/authorise";
        $this->api->request_token_url = "https://api.instagram.com/oauth/request_token";
        $this->api->access_token_url = "https://api.instagram.com/oauth/access_token";

//        if (isset($this->config['api_version']) && $this->config['api_version']) {
//            $this->api->api_base_url = "https://api.instagram.com/{$this->config['api_version']}/";
//        }

        if (isset($this->config['authorize']) && $this->config['authorize']) {
            $this->api->authorize_url = "https://api.instagram.com/oauth/authorize";
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
        $this->user->profile->profileURL = (property_exists($response, 'screen_name')) ? ("http://instagram.com/" . $response->screen_name) : null;
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

      
                    $contacts[] = $uc;
                }
            }
        }

        return $contacts;
    }

  

    
    /**
     * load the user's followers count    
     */
    function getFollowersCount($screen_name) {
            $response = $this->api->get('users/'.$screen_name);
     
        // check the last HTTP status code returned
        if ($this->api->http_code != 200) {
            throw new Exception("Followers count failed to load! {$this->providerId} returned an error. " . $this->errorMessageByStatus($this->api->http_code));
        }

        if (!$response) {
            return array();
        }

        $followers = $response;

        
         return $followers; exit;
        //return $followers['data']['counts']['followed_by'];
    }

}
