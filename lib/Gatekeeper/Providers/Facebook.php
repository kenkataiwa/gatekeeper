<?php

namespace Gatekeeper\Providers;

/**
 * Facebook
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
use Gatekeeper\Provider\Model\OAuth2;

class Facebook extends OAuth2 {

    private $scope = "email, user_about_me, user_birthday, user_hometown, user_website, read_stream, offline_access, publish_stream, read_friendlists";

    public function initialize() {
        if (!$this->config["keys"]["id"] || !$this->config["keys"]["secret"]) {
            throw new Exception("Your application id and secret are required in order to connect to {$this->providerId}.", 4);
        }

        $this->api = new Facebook(ARRAY('appId' => $this->config["keys"]["id"], 'secret' => $this->config["keys"]["secret"]));

		if ( $this->token("access_token") ) {
			$this->api->setAccessToken( $this->token("access_token") );
			$this->api->setExtendedAccessToken();
			$access_token = $this->api->getAccessToken();

			if( $access_token ){
				$this->token("access_token", $access_token );
				$this->api->setAccessToken( $access_token );
			}

			$this->api->setAccessToken( $this->token("access_token") );
		}

		$this->api->getUser();
    }

	/**
	* begin login step
	*
	* simply call Facebook::require_login().
	*/
	function loginBegin()
	{
		$parameters = array("scope" => $this->scope, "redirect_uri" => $this->endpoint, "display" => "page");
		$optionals  = array("scope", "redirect_uri", "display");

		foreach ($optionals as $parameter){
			if( isset( $this->config[$parameter] ) && ! empty( $this->config[$parameter] ) ){
				$parameters[$parameter] = $this->config[$parameter];
			}
		}

		// get the login url
		$url = $this->api->getLoginUrl( $parameters );

		// redirect to facebook
		Hybrid_Auth::redirect( $url );
	}

	/**
	* finish login step
	*/
	function loginFinish()
	{
		// in case we get error_reason=user_denied&error=access_denied
		if ( isset( $_REQUEST['error'] ) && $_REQUEST['error'] == "access_denied" ){
			throw new Exception( "Authentication failed! The user denied your request.", 5 );
		}

		// try to get the UID of the connected user from fb, should be > 0
		if ( ! $this->api->getUser() ){
			throw new Exception( "Authentication failed! {$this->providerId} returned an invalid user id.", 5 );
		}

		// set user as logged in
		$this->setUserConnected();

		// store facebook access token
		$this->token( "access_token", $this->api->getAccessToken() );
	}

	/**
	* logout
	*/
	function logout()
	{
		$this->api->destroySession();

		parent::logout();
	}

	/**
	* load the user profile from the IDp api client
	*/
	function getUserProfile()
	{
		// request user profile from fb api
		try{
			$data = $this->api->api('/me');
		}
		catch( FacebookApiException $e ){
			throw new Exception( "User profile request failed! {$this->providerId} returned an error: $e", 6 );
		}

		// if the provider identifier is not recived, we assume the auth has failed
		if ( ! isset( $data["id"] ) ){
			throw new Exception( "User profile request failed! {$this->providerId} api returned an invalid response.", 6 );
		}

		# store the user profile.
		$this->user->profile->identifier    = (array_key_exists('id',$data))?$data['id']:"";
		$this->user->profile->displayName   = (array_key_exists('name',$data))?$data['name']:"";
		$this->user->profile->firstName     = (array_key_exists('first_name',$data))?$data['first_name']:"";
		$this->user->profile->lastName      = (array_key_exists('last_name',$data))?$data['last_name']:"";
		$this->user->profile->photoURL      = "https://graph.facebook.com/" . $this->user->profile->identifier . "/picture?width=150&height=150";
		$this->user->profile->profileURL    = (array_key_exists('link',$data))?$data['link']:"";
		$this->user->profile->webSiteURL    = (array_key_exists('website',$data))?$data['website']:"";
		$this->user->profile->gender        = (array_key_exists('gender',$data))?$data['gender']:"";
		$this->user->profile->description   = (array_key_exists('bio',$data))?$data['bio']:"";
		$this->user->profile->email         = (array_key_exists('email',$data))?$data['email']:"";
		$this->user->profile->emailVerified = (array_key_exists('email',$data))?$data['email']:"";
		$this->user->profile->region        = (array_key_exists("hometown",$data)&&array_key_exists("name",$data['hometown']))?$data['hometown']["name"]:"";

		if( array_key_exists('birthday',$data) ) {
			list($birthday_month, $birthday_day, $birthday_year) = explode( "/", $data['birthday'] );

			$this->user->profile->birthDay   = (int) $birthday_day;
			$this->user->profile->birthMonth = (int) $birthday_month;
			$this->user->profile->birthYear  = (int) $birthday_year;
		}

		return $this->user->profile;
 	}

}

