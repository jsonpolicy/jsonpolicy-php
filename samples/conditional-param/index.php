<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Manager;

$manager = Manager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ]
]);

// Change APP_ENV value to see the difference
putenv('APP_ENV=2');

echo $manager->getParam('API:endpoint') . "\n";