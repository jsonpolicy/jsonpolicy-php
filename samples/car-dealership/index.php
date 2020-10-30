<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

// The main objective in this example is to determine the list of car that can be
// purchased based on the car price. The policy sets the threshold to $30,000.

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/dealership.php';
require __DIR__ . '/car.php';

use JsonPolicy\Manager as JsonPolicyManager;

$manager = JsonPolicyManager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ]
]);

// Build the inventory that is in the dealership's stock
$stock = array();
foreach (json_decode(file_get_contents(__DIR__ . '/inventory.json')) as $car) {
    $stock[] = new Car($car->model, $car->year, $car->price);
}

// Create the car dealership instance and pass the available list of cars for purchase
$dealership = new Dealership($stock);

// Check which car is allowed to be purchased based on policy attached to current
// identity
foreach ($dealership as $car) {
    if ($manager->isAllowedTo($car, 'purchase') === true) {
        echo "You can view and purchase {$car->model} ($car->year)\n";
    } else {
        echo "You cannot view and purchase {$car->model} ($car->year)\n";
    }
}