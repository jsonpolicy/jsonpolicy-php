<?php

namespace JSONPolicy\UnitTest\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "In" condition
 *
 * @version 0.0.1
 */
class InConditionTest extends TestCase
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
    public function testSingleCondition()
    {
        // Assert that left operand is in the array of values
        $this->assertTrue($this->evaluate([
            "In" => [
                "a" => ["b", "c", "a"]
            ]
        ]));

        // Assert that left operand is in the array of values with the same type
        $this->assertTrue($this->evaluate([
            "In" => [
                "5" => ["b", 5, "a"]
            ]
        ]));

        // Assert that left operand is not in the array of values
        $this->assertFalse($this->evaluate([
            "In" => [
                "d" => ["b", "c", "a"]
            ]
        ]));

        // Assert that left operand is not in the array of values because of the
        // type mismatch
        $this->assertFalse($this->evaluate([
            "In" => [
                "5" => ["b", "5", "a"]
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
    public function testMultipleConditionsWithOrOperator()
    {
        // Assert that at least one condition has match
        $this->assertTrue($this->evaluate([
            "In" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => ["world", "trust"]
            ]
        ]));

        // Assert that there are no matches in all conditions
        $this->assertFalse($this->evaluate([
            "In" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => ["this", "testing"],
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
    public function testMultipleConditionsWithAndOperator()
    {
        // Assert that at all conditions have match
        $this->assertTrue($this->evaluate([
            "In" => [
                "nope" => ["nope", "world"],
                "world" => [1, "world"]
            ]
        ]));

        // Assert that at least one condition has no match
        $this->assertFalse($this->evaluate([
            "In" => [
                "nope"  => ["nope", "world"],
                "hello" => ["a", "world"]
            ]
        ]));
    }

}