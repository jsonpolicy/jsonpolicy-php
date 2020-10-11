<?php

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