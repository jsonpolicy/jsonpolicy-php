<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Manager;

$manager = Manager::bootstrap([
    'repository' => [
        json_decode(file_get_contents(__DIR__  . '/policy.json'), true)
    ]
]);