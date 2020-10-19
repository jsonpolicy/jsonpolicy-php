<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "NotIn" condition
 *
 * @version 0.0.1
 */
class NotInConditionTest extends TestCase
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
        // Assert that left operand is not in the array of values
        $this->assertTrue(self::$condition->evaluate([
            "NotIn" => [
                "d" => ["b", "c", "a"]
            ]
        ], []));

        // Assert that left operand is in the array of values with the same type
        $this->assertFalse(self::$condition->evaluate([
            "NotIn" => [
                "5" => ["b", 5, "a"]
            ]
        ], []));

        // Assert that left operand is in the array of values
        $this->assertFalse(self::$condition->evaluate([
            "NotIn" => [
                "b" => ["b", "c", "a"]
            ]
        ], []));

        // Assert that left operand is in the array of values because of the
        // type mismatch
        $this->assertTrue(self::$condition->evaluate([
            "NotIn" => [
                "5" => ["b", "5", "a"]
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
    public function testMultipleConditionsWithOrOperator()
    {
        // Assert that at least one condition have no match
        $this->assertTrue(self::$condition->evaluate([
            "NotIn" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "this"     => ["this", "trust"]
            ]
        ], []));

        // Assert that all conditions actually have match
        $this->assertFalse(self::$condition->evaluate([
            "NotIn" => [
                "Operator" => "OR",
                "blah"     => ["blah", "world"],
                "bro"      => ["bro", 2]
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
    public function testMultipleConditionsWithAndOperator()
    {
        // Assert that at all conditions have no match
        $this->assertTrue(self::$condition->evaluate([
            "NotIn" => [
                "nope"  => ["this", "world"],
                "world" => [1, "testing"]
            ]
        ], []));

        // Assert that at least one condition has match
        $this->assertFalse(self::$condition->evaluate([
            "NotIn" => [
                "nope"  => ["nope", "world"],
                "hello" => ["a", "hello"]
            ]
        ], []));
    }

}