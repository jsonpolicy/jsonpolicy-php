<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "LessOrEquals" condition
 *
 * @version 0.0.1
 */
class LessOrEqualsConditionTest extends TestCase
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
            "LessOrEquals" => [
                "A" => "D"
            ]
        ], []));

        // Assert that the left operand is equals than the right
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
                "5" => 5
            ]
        ], []));

        // Assert that the left operand is greater than the right
        $this->assertFalse(self::$condition->evaluate([
            "LessOrEquals" => [
                "20" => 15
            ]
        ], []));

        // Assert that numeric values are equal despite the difference in types
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
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
        // Assert that left operand is equals than at least one value
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
                "2020-10-01" => ["2020-11-01", "2020-10-01"]
            ]
        ], []));

        // Assert that left operand is less than at least one value
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
                "2020-10-01" => ["2020-11-01", "2020-07-01"]
            ]
        ], []));

        // Assert that left operand is greater or and not equals to all the values
        $this->assertFalse(self::$condition->evaluate([
            "LessOrEquals" => [
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
            "LessOrEquals" => [
                "Operator"   => "OR",
                "A"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ], []));

        // Assert that at least in one condition the left operand is equals
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
                "Operator"   => "OR",
                "B"          => ["B", "C"],
                "2020-01-01" => "2019-10-01"
            ]
        ], []));

        // Assert that in all the conditions, the left operand is greater than right
        $this->assertFalse(self::$condition->evaluate([
            "LessOrEquals" => [
                "Operator" => "OR",
                "Z"        => ["B", "C"],
                "50"        => 15,
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
            "LessOrEquals" => [
                "A" => ["B", "C"],
                "2" => "10"
            ]
        ], []));

        // Assert that in all conditions the left operand is equals
        $this->assertTrue(self::$condition->evaluate([
            "LessOrEquals" => [
                "B"  => ["A", "B"],
                "10" => "10"
            ]
        ], []));

        // Assert that at at least in one condition the left operand is greater
        $this->assertFalse(self::$condition->evaluate([
            "LessOrEquals" => [
                "Z"  => ["A", "B"],
                "10" => 30
            ]
        ], []));
    }

}