<?php

namespace JSONPolicy\UnitTest\Parser;

use JsonPolicy\Core\Entity,
    JsonPolicy\Core\Context,
    PHPUnit\Framework\TestCase,
    JsonPolicy\Parser\ConditionParser;

class ConditionParserTest extends TestCase
{

    /**
     * Test that parser returns expected tokenized array
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testParserWithoutLogicalOperand()
    {
        $result = ConditionParser::parse([
            'Equals' => [
                5 => 5
            ],
            'NotEquals' => [
                1 => 2
            ]
            ], new Context);

        $this->assertArrayHasKey('Equals', $result);
        $this->assertArrayHasKey('5', $result['Equals']);
        $this->assertArrayHasKey('left', $result['Equals'][5]);
        $this->assertArrayHasKey('right', $result['Equals'][5]);
        $this->assertEquals(Entity::class, get_class($result['Equals'][5]['left']));
        $this->assertEquals(Entity::class, get_class($result['Equals'][5]['right']));

        $this->assertArrayHasKey('NotEquals', $result);
        $this->assertArrayHasKey('1', $result['NotEquals']);
        $this->assertArrayHasKey('left', $result['NotEquals'][1]);
        $this->assertArrayHasKey('right', $result['NotEquals'][1]);
    }

    /**
     * Test that parser returns expected tokenized array and persists Operator
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testParserWithLogicalOperand()
    {
        $result = ConditionParser::parse([
            'Operator' => 'AND',
            'Equals' => [
                'Operator' => 'OR',
                5 => 5
            ]
        ], new Context);

        $this->assertArrayHasKey('Operator', $result);
        $this->assertEquals('AND', $result['Operator']);

        $this->assertArrayHasKey('Equals', $result);
        $this->assertArrayHasKey('5', $result['Equals']);

        $this->assertArrayHasKey('Operator', $result['Equals']);
        $this->assertEquals('OR', $result['Equals']['Operator']);

        $this->assertArrayHasKey('left', $result['Equals'][5]);
        $this->assertArrayHasKey('right', $result['Equals'][5]);
        $this->assertEquals(Entity::class, get_class($result['Equals'][5]['left']));
        $this->assertEquals(Entity::class, get_class($result['Equals'][5]['right']));
    }

}