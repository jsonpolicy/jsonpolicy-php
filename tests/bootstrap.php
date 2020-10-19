<?php

// Register autoloader
spl_autoload_register(function ($class_name) {
    $filepath = null;

    if (strpos($class_name, 'JSONPolicy\UnitTest') === 0) {
        $filepath = __DIR__ . str_replace(array('JSONPolicy\UnitTest', '\\'), array('', '/'), $class_name) . '.php';
    }

    if ($filepath && file_exists($filepath)) {
        require_once $filepath;
    }
});