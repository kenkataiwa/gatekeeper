<?php

namespace Sandbox;

use \Gatekeeper\Auth;

spl_autoload_register(function($className) {
            $className = (string) str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $className .= '.php';
            $className = __DIR__ . '/../../lib/' . $className;
            if (file_exists($className)) {
                require_once $className;
            }
        });

$params = array(
    'redirect_url' => 'http://www.example.com'
);

try {
    $auth = new Auth(require __DIR__ . '/config/services.config.php');
    $facebook = $auth->authenticate('facebook', $params);
} catch (\Exception $e) {
    die("<b>got an error!</b> " . $e->getMessage());
}

var_dump($auth);