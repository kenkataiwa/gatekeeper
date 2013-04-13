<?php

namespace Sandbox;

spl_autoload_register(function($className) {
            $className = (string) str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $className .= '.php';
            $className = __DIR__ . '/../../lib/' . $className;
            if (file_exists($className)) {
                require_once $className;
            }
        });

use \Gatekeeper\Auth;

try {
    $auth = new Auth(require __DIR__ . '/config/services.config.php');
//    $adapter = $auth->authenticate('facebook', array());
//    $userProfile = $adapter->getUserProfile();
} catch (\Exception $e) {
    die("<b>got an error!</b> " . $e->getMessage());
}

print_r( $auth );

//print_r($userProfile);