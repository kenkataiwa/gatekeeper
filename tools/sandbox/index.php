<?php

require __DIR__ . '/../bootstrap.php';

try {
    $auth = new \Gatekeeper\Auth(require __DIR__ . '/config/services.config.php');
    $service = $auth->authenticate('twitter');
    $user = $service->getUserProfile();
} catch (\Exception $e) {
    //var_dump($e);
    die("<b>Got an error!</b> " . $e->getMessage());
}

var_dump($user);

echo 'Finished execution';
