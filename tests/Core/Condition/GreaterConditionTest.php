<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Greater" condition
 *
 * @version 0.0.1
 */
class GreaterConditionTest extends TestCase
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
        $this->assertTrue(self::$condition->evaluate([
            "Greater" => [
                "D" => "A"
            ]
        ], []));

        // Assert that the left operand is not greater than the right
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "15" => 20
            ]
        ], []));

        // Assert that values are equal so can't be greater
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "5" => 5
            ]
        ], []));

        // Assert that numeric values are equal despite the difference in types
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "5" => "5"
            ]
        ], []));
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
        // Assert that left operand is greater than at least one value
        $this->assertTrue(self::$condition->evaluate([
            "Greater" => [
                "2020-10-01" => ["2020-11-01", "2020-09-17"]
            ]
        ], []));

        // Assert that left operand is not greater than all the values
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "16" => ["20", 30, "21"]
            ]
        ], []));
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
        $this->assertTrue(self::$condition->evaluate([
            "Greater" => [
                "Operator"   => "OR",
                "A"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ], []));

        // Assert that in all the conditions, the left operand is less then right
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "Operator" => "OR",
                "A"        => ["B", "C"],
                "5"        => 15,
                "2020"     => [2021, 2022]
            ]
        ], []));
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
        $this->assertTrue(self::$condition->evaluate([
            "Greater" => [
                "Z"  => ["A", "B"],
                "20" => "10"
            ]
        ], []));

        // Assert that at at least in one condition the left operand is less
        $this->assertFalse(self::$condition->evaluate([
            "Greater" => [
                "Z"  => ["A", "B"],
                "20" => 30
            ]
        ], []));
    }

}