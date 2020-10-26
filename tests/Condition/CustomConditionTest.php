<?php

namespace JSONPolicy\UnitTest\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Similar" custom condition
 *
 * @version 0.0.1
 */
class CustomConditionTest extends TestCase
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
        $this->assertTrue($this->evaluate([
            "Similar" => [
                "hello" => "hello"
            ]
        ]));

        // Assert that values are different
        $this->assertFalse($this->evaluate([
            "Similar" => [
                "warm" => "world"
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
        // Assert that left operand is similar to at least one value
        $this->assertTrue($this->evaluate([
            "Similar" => [
                "hello" => ["hello", "world"]
            ]
        ]));

        // Assert that left operand is not similar to any values
        $this->assertFalse($this->evaluate([
            "Similar" => [
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
        // Assert that at least one condition has similar pair
        $this->assertTrue($this->evaluate([
            "Similar" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "world"
            ]
        ]));

        // Assert that there are no similar pairs in all conditions
        $this->assertFalse($this->evaluate([
            "Similar" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "this"
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
        $this->assertTrue($this->evaluate([
            "Similar" => [
                "nope" => ["nope", "world"],
                "world" => "world"
            ]
        ]));

        // Assert that at at least one condition has no identical pair
        $this->assertFalse($this->evaluate([
            "Similar" => [
                "nope"  => ["nope", "world"],
                "hello" => "world"
            ]
        ]));
    }

}