<?php

require __DIR__.'/../bootstrap.php';

$params = array(
    'redirect_url' => 'http://www.example.com'
);

try {
    $auth = new \Gatekeeper\Auth(require __DIR__ . '/config/services.config.php');
    $facebook = $auth->authenticate('facebook', $params);
} catch (\Exception $e) {
    die("<b>got an error!</b> " . $e->getMessage());
}

var_dump($auth);