<?php

require __DIR__ . '/../bootstrap.php';

try {
    $params = ['business' => 'http://www.marketplace.co.tz/CenturyCinemax'];
    $auth = new \Gatekeeper\Auth(require __DIR__ . '/config/services.config.php');
    $service = $auth->authenticate('facebook');
    //$p = $service->publishUserAction('marketplacetz:review', $params);
} catch (\Exception $e) {
    //var_dump($e);
    die("<b>Got an error!</b> " . $e->getMessage());
}

var_dump($p);

echo 'Finished execution';
