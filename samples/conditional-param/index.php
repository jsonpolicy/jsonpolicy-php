<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Manager;

$manager = Manager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ]
]);

print_r($manager->getParam('API:endpoint'));