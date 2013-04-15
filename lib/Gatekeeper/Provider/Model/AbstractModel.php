<?php

namespace Gatekeeper\Provider\Model;

use Gatekeeper\User,
    Gatekeeper\Storage,
    \Exception;

/**
 *
 * @author Kenneth Kataiwa <kenkataiwa@gmail.com>
 */
abstract class AbstractModel {

    /**
     * The provider api client (optional)
     */
    public $api = NULL;

    /**
     * Storage
     */
    public $storage;

    /**
     * Common service providers adapter constructor
     */
    public function __construct($providerId, $config, $params = NULL) {
        # init the IDp adapter parameters, get them from the cache if possible
        if (!$params) {
            $this->params = $this->storage->get("gk_session.$providerId.id_provider_params");
        } else {
            $this->params = $params;
        }

        // idp id
        $this->providerId = $providerId;

        $this->storage = new Storage;

        // set HybridAuth endpoint for this provider
        $this->endpoint = $this->storage->get("gk_session.$providerId.gk_endpoint");

        // idp config
        $this->config = $config;

        // new user instance
        $this->user = new User;
        $this->user->providerId = $providerId;

        // initialize the current provider adapter
        $this->initialize();
    }

    /**
     * IDp wrappers initializer
     *
     * The main job of wrappers initializer is to performs (depend on the IDp api client it self):
     *     - include some libs nedded by this provider,
     *     - check IDp key and secret,
     *     - set some needed parameters (stored in $this->params) by this IDp api client
     *     - create and setup an instance of the IDp api client on $this->api
     *
     * @return Object
     */
    abstract protected function initialize();

    /**
     * Begin login
     */
    abstract protected function loginBegin();

    /**
     * Finish login
     */
    abstract protected function loginFinish();

    /**
     * generic logout, just erase current provider adapter stored data to let Hybrid_Auth all forget about it
     */
    function logout() {
        $this->clearTokens();

        return TRUE;
    }

    /**
     * Grab the user profile from the IDp api client
     */
    function getUserProfile() {
        throw new Exception("Provider does not support this feature.", 8);
    }

    /**
     * Return true if the user is connected to the current provider
     */
    public function isUserConnected() {
        return (bool) $this->storage->get("gk_session.{$this->providerId}.is_logged_in");
    }

    /**
     * Set user to connected
     */
    public function setUserConnected() {
        $this->storage->set("gk_session.{$this->providerId}.is_logged_in", 1);
    }

    /**
     * Set user to unconnected
     */
    public function setUserUnconnected() {
        $this->storage->set("gk_session.{$this->providerId}.is_logged_in", 0);
    }

    /**
     * get or set a token
     */
    public function token($token, $value = NULL) {
        if ($value === NULL) {
            return $this->storage->get("gk_session.{$this->providerId}.token.$token");
        } else {
            $this->storage->set("gk_session.{$this->providerId}.token.$token", $value);
        }
    }

    /**
     * delete a stored token
     */
    public function deleteToken($token) {
        $this->storage->delete("gk_session.{$this->providerId}.token.$token");
    }

    /**
     * clear all existen tokens for this provider
     */
    public function clearTokens() {
        $this->storage->deleteMatch("gk_session.{$this->providerId}.");
    }

}