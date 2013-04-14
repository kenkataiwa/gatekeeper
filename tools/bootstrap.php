<?php

/**
 * Requiring composer dependencies autoloader
 */
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function($className) {
            $className = (string) str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $className .= '.php';
            $className = __DIR__ . '/../lib/' . $className;
            if (file_exists($className)) {
                require_once $className;
            }
        });