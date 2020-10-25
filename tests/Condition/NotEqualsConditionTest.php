<?php

namespace JSONPolicy\UnitTest\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "NotEquals" condition
 *
 * @version 0.0.1
 */
class NotEqualsConditionTest extends TestCase
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
        // Assert that values are not identical
        $this->assertTrue($this->evaluate([
            "NotEquals" => [
                "hello" => "hello world"
            ]
        ]));

        // Assert that values are the same
        $this->assertFalse($this->evaluate([
            "NotEquals" => [
                "hello" => "hello"
            ]
        ]));

        // Assert that values are equal if integers
        $this->assertFalse($this->evaluate([
            "NotEquals" => [
                "5" => 5
            ]
        ]));

        // Assert that numeric values are not equal if right value is string
        $this->assertTrue($this->evaluate([
            "NotEquals" => [
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
        // Assert that left operand is identical to at least one value
        $this->assertFalse($this->evaluate([
            "NotEquals" => [
                "hello" => ["hello", "world"]
            ]
        ]));

        // Assert that left operand is not identical to any values
        $this->assertTrue($this->evaluate([
            "NotEquals" => [
                "hello" => ["this", "world"]
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
        // Assert that at least one condition has identical pair
        $this->assertFalse($this->evaluate([
            "NotEquals" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "world"
            ]
        ]));

        // Assert that there are no identical pairs in all conditions
        $this->assertTrue($this->evaluate([
            "NotEquals" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "this",
                "testing"  => [1, 2]
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
        // Assert that at all conditions have identical pairs
        $this->assertFalse($this->evaluate([
            "NotEquals" => [
                "nope" => ["nope", "world"],
                "world" => "world"
            ]
        ]));

        // Assert that at at least one condition has no identical pair
        $this->assertTrue($this->evaluate([
            "NotEquals" => [
                "nope"  => ["nope", "world"],
                "hello" => "world"
            ]
        ]));
    }

}