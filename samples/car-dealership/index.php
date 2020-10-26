<?php

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/dealership.php';
require __DIR__ . '/car.php';

use JsonPolicy\Manager;

$manager = Manager::bootstrap([
    'repository' => [
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
        echo "You can view or purchase {$car->model} ($car->year)\n";
    } else {
        echo "You cannot view and purchase {$car->model} ($car->year)\n";
    }
}