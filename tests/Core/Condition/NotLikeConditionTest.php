<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "NotLike" condition
 *
 * @version 0.0.1
 */
class NotLikeConditionTest extends TestCase
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
        // Assert that values are not similar
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "hello" => "world*"
            ]
        ], []));

        // Assert that values are not similar with asterisk in the middle
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "carson" => "he*o"
            ]
        ], []));

        // Assert that values are not similar with asterisk at the beginning
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "table" => "*o"
            ]
        ], []));

        // Assert that values are not similar with newline
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "he\nllo" => "m*o*"
            ]
        ], []));

        // Assert that values are not exact
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "hello" => "world"
            ]
        ], []));

        // Assert that values are not similar with some special RegExp symbols
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "dance#\\*(0.)" => "test#*"
            ]
        ], []));

        // Assert that values are similar
        $this->assertFalse(self::$condition->evaluate([
            "NotLike" => [
                "nope" => "n*"
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
        // Assert that left operand is not similar to all values in the array
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "ted" => ["be*", "r*"]
            ]
        ], []));

        // Assert that left operand is similar actually to all values in array
        $this->assertFalse(self::$condition->evaluate([
            "NotLike" => [
                "blah" => ["b*", "blah"]
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
        // Assert that there is at least one condition that does not have similarities
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "Operator" => "OR",
                "hello"    => ["hello", "world"],
                "world"    => "*bra"
            ]
        ], []));

        // Assert that all conditions have similarity
        $this->assertFalse(self::$condition->evaluate([
            "NotLike" => [
                "Operator" => "OR",
                "hello"    => ["h*", "hello"],
                "world"    => "w*",
                "testing"  => ["test*"]
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
        // Assert that at all conditions have no similarities
        $this->assertTrue(self::$condition->evaluate([
            "NotLike" => [
                "nope"    => ["apple", "world", "*a"],
                "world"   => "rainbow",
                "testing" => ["d*a", 1, "many"]
            ]
        ], []));

        // Assert that at least one has similarity detected
        $this->assertFalse(self::$condition->evaluate([
            "NotLike" => [
                "nope"  => ["*bl", "world"],
                "hello" => "h*"
            ]
        ], []));
    }

}