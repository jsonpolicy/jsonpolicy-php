<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "Like" condition
 *
 * @version 0.0.1
 */
class LikeConditionTest extends TestCase
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
        // Assert that values are similar
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "hello" => "hel*"
            ]
        ], []));

        // Assert that values are similar with asterisk in the middle
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "hello" => "he*o"
            ]
        ], []));

        // Assert that values are similar with asterisk at the beginning
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "hello" => "*o"
            ]
        ], []));

        // Assert that values are similar with newline
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "he\nllo" => "*o"
            ]
        ], []));

        // Assert that values are exact
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "hello" => "hello"
            ]
        ], []));

        // Assert that values are similar with some special RegExp symbols
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "test#\\*(0.)" => "test#*"
            ]
        ], []));

        // Assert that values are not similar
        $this->assertFalse(self::$condition->evaluate([
            "Like" => [
                "nope" => "cal*"
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
        // Assert that left operand is similar to at least one value
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "role" => ["te*", "r*"]
            ]
        ], []));

        // Assert that left operand is not similar to any values
        $this->assertFalse(self::$condition->evaluate([
            "Like" => [
                "blah" => ["a*", "r*lo"]
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
        // Assert that at least one condition detects similarity
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "Operator" => "OR",
                "hello"    => ["nope", "world"],
                "world"    => "*rld"
            ]
        ], []));

        // Assert that there are no similarities in all conditions
        $this->assertFalse(self::$condition->evaluate([
            "Like" => [
                "Operator" => "OR",
                "hello"    => ["nope", "worl*"],
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
        // Assert that at all conditions have similarities
        $this->assertTrue(self::$condition->evaluate([
            "Like" => [
                "nope"    => ["nope", "world"],
                "world"   => "w*ld",
                "testing" => ["t*"]
            ]
        ], []));

        // Assert that at at least one condition has no similarity
        $this->assertFalse(self::$condition->evaluate([
            "Like" => [
                "nope"  => ["*ope", "world"],
                "hello" => "worl*"
            ]
        ], []));
    }

}