<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Less" condition
 *
 * @version 0.0.1
 */
class LessConditionTest extends TestCase
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
        // Assert that the left operand is less than the right
        $this->assertTrue(self::$condition->evaluate([
            "Less" => [
                "A" => "D"
            ]
        ], []));

        // Assert that the left operand is greater than the right
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
                "20" => 15
            ]
        ], []));

        // Assert that values are equal so can't be less
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
                "5" => 5
            ]
        ], []));

        // Assert that numeric values are equal despite the difference in types, so
        // they can't be less
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
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
        // Assert that left operand is less than at least one value
        $this->assertTrue(self::$condition->evaluate([
            "Less" => [
                "2020-10-01" => ["2020-11-01", "2020-09-17"]
            ]
        ], []));

        // Assert that left operand is greater than all the values
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
                "40" => ["20", 30, "21"]
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
        // Assert that at least in one condition the left operand is less
        $this->assertTrue(self::$condition->evaluate([
            "Less" => [
                "Operator"   => "OR",
                "A"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ], []));

        // Assert that in all the conditions, the left operand is greater then right
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
                "Operator" => "OR",
                "Z"        => ["B", "C"],
                "55"        => 15,
                "2025"     => [2021, 2022]
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
        // Assert that in all conditions the left operand is less
        $this->assertTrue(self::$condition->evaluate([
            "Less" => [
                "A"  => ["S", "T"],
                "20" => "100"
            ]
        ], []));

        // Assert that at least in one condition the left operand is greater
        $this->assertFalse(self::$condition->evaluate([
            "Less" => [
                "Z"  => ["A", "B"],
                "20" => 30
            ]
        ], []));
    }

}