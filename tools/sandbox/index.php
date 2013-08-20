<?php

require __DIR__ . '/../bootstrap.php';

try {
    $auth = new \Gatekeeper\Auth(require __DIR__ . '/config/services.config.php');
    $google = $auth->authenticate('twitter');
    $contacts = $google->getUserContacts();
//    $contact = $google->getUserContact();
} catch (\Exception $e) {
    die("<b>got an error!</b> " . $e->getMessage());
}

var_dump($contacts);
