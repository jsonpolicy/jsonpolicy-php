<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use PHPUnit\Framework\TestCase;

/**
 * Testing "RegEx" condition
 *
 * @version 0.0.1
 */
class RegExConditionTest extends TestCase
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
        // Assert that value matches the regex
        $this->assertTrue(self::$condition->evaluate([
            "RegEx" => [
                "PO00001" => "^PO[\\d]+$"
            ]
        ], []));

        // Assert that values does not match the regex
        $this->assertFalse(self::$condition->evaluate([
            "RegEx" => [
                "2020T13:00" => "/[\\d-]{10}T.*$/i"
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
        // Assert that left operand matches at least on regex
        $this->assertTrue(self::$condition->evaluate([
            "RegEx" => [
                "cart" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"]
            ]
        ], []));

        // Assert that left operand does not match any regex
        $this->assertFalse(self::$condition->evaluate([
            "RegEx" => [
                "@@@hello" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"]
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
        // Assert that at least one condition matches
        $this->assertTrue(self::$condition->evaluate([
            "RegEx" => [
                "Operator" => "OR",
                "@@@hello" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"],
                "PO00001"  => "^PO[\\d]+$"
            ]
        ], []));

        // Assert that there are no identical pairs in all conditions
        $this->assertFalse(self::$condition->evaluate([
            "RegEx" => [
                "Operator" => "OR",
                "@@@hello" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"],
                "SALE00001"  => "^PO[\\d]+$"
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
        // Assert that at all conditions have matches
        $this->assertTrue(self::$condition->evaluate([
            "RegEx" => [
                "cart" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"],
                "PO00001" => "^PO[\\d]+$"
            ]
        ], []));

        // Assert that at at least one condition does not match
        $this->assertFalse(self::$condition->evaluate([
            "RegEx" => [
                "@@@hello" => ["[a-z]{1}-[\\d]+", "/^[a-z]+$/i"],
                "PO00001"  => "^PO[\\d]+$"
            ]
        ], []));
    }

}