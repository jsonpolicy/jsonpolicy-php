<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

// Declaring custom condition IsSet that check if a single value is not empty or
// at least on element in the array of values is not empty

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Manager as PolicyManager;

$manager = PolicyManager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ],
    'custom_conditions' => [
        'NotEmpty' => function($group, $operator, $manager) {
            $result = null;

            foreach ($group as $value) {
                $result = $manager->compute($result, !empty($value['right']), 'OR');
            }

            return $result;
        }
    ]
]);

// Uncomment the following like to change the outcome of the conditional check below
//$_COOKIE['authenticated'] = 1;

if ($manager->isAllowed('backend')) {
    echo 'Yes. You are authenticated';
} else {
    echo 'No, you cannot access the backend';
}