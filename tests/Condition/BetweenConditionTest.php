<?php

namespace JSONPolicy\UnitTest\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Between" condition
 *
 * @version 0.0.1
 */
class BetweenConditionTest extends TestCase
{

    use ConditionTrait;

    /**
     * Test a single range conditions
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleConditionOneRange()
    {
        // Assert that value is actually within range
        $this->assertTrue($this->evaluate([
            "Between" => [
                "5" => [4, 10]
            ]
        ]));

        // Assert that value is NOT within range
        $this->assertFalse($this->evaluate([
            "Between" => [
                "11" => [4, 10]
            ]
        ]));

        // Assert that value is actually within range on the left edge of it
        $this->assertTrue($this->evaluate([
            "Between" => [
                "4" => [4, 10]
            ]
        ]));

        // Assert that value is actually within range on the right edge of it
        $this->assertTrue($this->evaluate([
            "Between" => [
                "10" => [4, 10]
            ]
        ]));
    }

    /**
     * Test a single condition with multiple ranges
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleConditionMultipleRanges()
    {
        // Assert that value is actually within range
        $this->assertTrue($this->evaluate([
            "Between" => [
                "25" => [[4, 10],[20, 30]]
            ]
        ]));

        // Assert that value is NOT within range
        $this->assertFalse($this->evaluate([
            "Between" => [
                "14" => [[4, 10],[20, 30]]
            ]
        ]));

        // Assert that value is actually within range on the left edge of it
        $this->assertTrue($this->evaluate([
            "Between" => [
                "4" => [[4, 10],[20, 30]]
            ]
        ]));

        // Assert that value is actually within range on the right edge of it
        $this->assertTrue($this->evaluate([
            "Between" => [
                "30" => [[4, 10],[20, 30]]
            ]
        ]));
    }

    /**
     * Test a multiple conditions with mixed ranges and OR operators
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleConditionsMixedRangeOrOperator()
    {
        // Assert that value is within range in at least one condition
        $this->assertTrue($this->evaluate([
            "Between" => [
                "Operator" => "OR",
                "5"  => [4, 10],
                "10" => [[1, 4], [20, 30]]
            ]
        ]));

        // Assert that value is NOT within any range
        $this->assertFalse($this->evaluate([
            "Between" => [
                "Operator" => "OR",
                "3"  => [4, 10],
                "10" => [[1, 4], [20, 30]]
            ]
        ]));
    }

    /**
     * Test a multiple conditions with mixed ranges and AND operators
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleConditionsMixedRangeAndOperator()
    {
        // Assert that value is within range in all ranges in conditions
        $this->assertTrue($this->evaluate([
            "Between" => [
                "5"  => [4, 10],
                "21" => [[1, 4], [20, 30]]
            ]
        ]));

        // Assert that value is NOT within any all the ranges in conditions
        $this->assertFalse($this->evaluate([
            "Between" => [
                "11"  => [4, 10],
                "3" => [[1, 4], [20, 30]]
            ]
        ]));
    }

}