<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/member.php';

use JsonPolicy\Manager as PolicyManager;

$manager = PolicyManager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ],
    'custom_effects' => [
        'attached' => 'attach'
    ]
]);

$member = new Member([
    'name'   => 'John Smith',
    'groups' => [
        'advanced',
        'rock-climber'
    ]
]);

$badges = [
    'super-badge',
    'advanced-badge',
    'smart-badge'
];

foreach($badges as $badge) {
    if ($manager->isAttached($badge, false, $member)) {
        echo "The {$badge} is attached\n";
    } else {
        echo "The {$badge} is not attached\n";
    }
}