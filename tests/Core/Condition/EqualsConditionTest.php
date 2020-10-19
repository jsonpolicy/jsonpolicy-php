<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Equals" condition
 *
 * @version 0.0.1
 */
class EqualsConditionTest extends TestCase
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
        // Assert that values are identical
        $this->assertTrue(self::$condition->evaluate([
            "Equals" => [
                "hello" => "hello"
            ]
        ], []));

        // Assert that values are different
        $this->assertFalse(self::$condition->evaluate([
            "Equals" => [
                "hello" => "world"
            ]
        ], []));

        // Assert that values are equal if integers
        $this->assertTrue(self::$condition->evaluate([
            "Equals" => [
                "5" => 5
            ]
        ], []));

        // Assert that numeric values are not equal if right value is string
        $this->assertFalse(self::$condition->evaluate([
            "Equals" => [
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
        // Assert that left operand is identical to at least one value
        $this->assertTrue(self::$condition->evaluate([
            "Equals" => [
                "hello" => ["hello", "world"]
            ]
        ], []));

        // Assert that left operand is not identical to any values
        $this->assertFalse(self::$condition->evaluate([
            "Equals" => [
                "hello" => ["this", "world"]
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
        // Assert that at least one condition has identical pair
        $this->assertTrue(self::$condition->evaluate([
            "Equals" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "world"
            ]
        ], []));

        // Assert that there are no identical pairs in all conditions
        $this->assertFalse(self::$condition->evaluate([
            "Equals" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "this",
                "testing"  => [1, 2]
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
        // Assert that at all conditions have identical pairs
        $this->assertTrue(self::$condition->evaluate([
            "Equals" => [
                "nope" => ["nope", "world"],
                "world" => "world"
            ]
        ], []));

        // Assert that at at least one condition has no identical pair
        $this->assertFalse(self::$condition->evaluate([
            "Equals" => [
                "nope"  => ["nope", "world"],
                "hello" => "world"
            ]
        ], []));
    }

}