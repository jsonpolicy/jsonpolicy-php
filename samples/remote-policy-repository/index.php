<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Manager as PolicyManager;

$manager = PolicyManager::bootstrap([
    'policies' => function() {
        return [
            file_get_contents('http://dev.wordpress/policy.json')
        ];
    }
]);

if ($manager->isAllowed('registration', true)) {
    echo "The registration is available\n";
    echo "Registration endpoint is " . $manager->getParam('registration-endpoint');
} else {
    echo 'No, the registration is disabled';
}