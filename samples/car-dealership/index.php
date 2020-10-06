<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

use JsonPolicy\Facade;

$facade = Facade::bootstrap([
    'identity'   => null,
    'repository' => [
        'sample-policy' => json_decode(file_get_contents(__DIR__  . '/policy.json'), true)
    ]
]);

/**
 * Car Dealership
 *
 */
class CarDealership implements Iterator
{

    /**
     * Dealership stock
     *
     * @var array
     *
     * @access protected
     */
    protected $in_stock = [];

    /**
     * Constructor
     *
     * @param array $cars
     */
    public function __construct(array $cars = [])
    {
        $this->in_stock = $cars;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return next($this->in_stock);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $key = key($this->in_stock);

        return ($key !== null && $key !== false);
    }

}

/**
 * Car class
 *
 */
class Car
{

    /**
     * Car model
     *
     * @var string
     */
    public $model;

    /**
     * Car year
     *
     * @var int
     */
    public $year;

    /**
     * Car price
     *
     * @var float
     */
    public $price = 0;

    /**
     * Constructor
     *
     * @param string  $model
     * @param integer $year
     * @param float   $price
     *
     * @return void
     */
    public function __construct(string $model, int $year, float $price)
    {
        $this->model = $model;
        $this->year  = $year;
        $this->price = $price;
    }

}

// Build the inventory that is in the dealership's stock
$stock = array();

foreach (json_decode(file_get_contents(__DIR__ . '/inventory.json')) as $car) {
    $stock[] = new Car($car->model, $car->year, $car->price);
}

// Create the car dealership instance and pass the available list of cars for purchase
$dealership = new CarDealership($stock);

// Check which car is allowed to be purchased based on policy(s) attached to current
// identity
foreach ($dealership as $car) {
    if ($facade->isAllowed($car, 'purchase') === true) {
        echo "You can purchase {$car->model} ($car->year)\n";
    } else {
        echo "You cannot purchase {$car->model} ($car->year)\n";
    }
}