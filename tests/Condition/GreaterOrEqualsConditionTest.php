<?php

namespace JSONPolicy\UnitTest\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "GreaterOrEquals" condition
 *
 * @version 0.0.1
 */
class GreaterOrEqualsConditionTest extends TestCase
{

    use ConditionTrait;

    /**
     * Test a single condition with one value
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleConditionOneValue()
    {
        // Assert that the left operand is greater than the right
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "D" => "A"
            ]
        ]));

        // Assert that the left operand is equals than the right
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "5" => 5
            ]
        ]));

        // Assert that the left operand is not greater than the right
        $this->assertFalse($this->evaluate([
            "GreaterOrEquals" => [
                "15" => 20
            ]
        ]));

        // Assert that numeric values are equal despite the difference in types
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "5" => "5"
            ]
        ]));
    }

    /**
     * Test a single condition with multiple value
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleConditionMultipleValues()
    {
        // Assert that left operand is equals than at least one value
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "2020-10-01" => ["2020-11-01", "2020-10-01"]
            ]
        ]));

        // Assert that left operand is greater than at least one value
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "2020-10-01" => ["2020-11-01", "2020-07-01"]
            ]
        ]));

        // Assert that left operand is not greater or equals than all the values
        $this->assertFalse($this->evaluate([
            "GreaterOrEquals" => [
                "16" => ["20", 30, "21"]
            ]
        ]));
    }

    /**
     * Testing multiple conditions with OR operator
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleConditionsMixedRightOperandWithOrOperator()
    {
        // Assert that at least in one condition the left operand is greater
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "Operator"   => "OR",
                "A"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ]));

        // Assert that at least in one condition the left operand is equals
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "Operator"   => "OR",
                "B"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ]));

        // Assert that in all the conditions, the left operand is less then right
        $this->assertFalse($this->evaluate([
            "GreaterOrEquals" => [
                "Operator" => "OR",
                "A"        => ["B", "C"],
                "5"        => 15,
                "2020"     => [2021, 2022]
            ]
        ]));
    }

    /**
     * Testing multiple conditions with default AND operator
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleConditionsMixedRightOperandWithAndOperator()
    {
        // Assert that in all conditions the left operand is greater
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "Z"  => ["A", "B"],
                "20" => "10"
            ]
        ]));

        // Assert that in all conditions the left operand is equals
        $this->assertTrue($this->evaluate([
            "GreaterOrEquals" => [
                "B"  => ["A", "B"],
                "10" => "10"
            ]
        ]));

        // Assert that at at least in one condition the left operand is less
        $this->assertFalse($this->evaluate([
            "GreaterOrEquals" => [
                "Z"  => ["A", "B"],
                "20" => 30
            ]
        ]));
    }

}