<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/page.php';

use JsonPolicy\Manager as PolicyManager;

$manager = PolicyManager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ],
    'custom_resources' => [
        function($name, $resource) {
            if (is_null($name) && is_a($resource, 'Page')) {
                $name = "Page:{$resource->slug}";
            }

            return $name;
        }
    ]
]);

// Change the slug to see the difference
$page = new Page([
    'ID'    => 124,
    'slug'  => 'private-portal'
]);

if ($manager->isAllowed($page, true)) {
    echo 'Yes. You can access this page';
} else {
    echo 'No. You cannot access this page';
}