<?php

namespace JSONPolicy\UnitTest\Condition;

use JsonPolicy\Core\Context,
    JsonPolicy\Parser\ConditionParser,
    JsonPolicy\Parser\ExpressionParser,
    JsonPolicy\Manager\ConditionManager;

/**
 * Common setup for the conditions testing
 *
 * @version 0.0.1
 */
trait ConditionTrait
{

    /**
     * Condition manager instance
     *
     * @var JsonPolicy\Core\Condition
     *
     * @access protected
     * @version 0.0.1
     */
    protected static $manager;

    /**
     * Setup the testing class
     *
     * @return void
     *
     * @access public
     * @static
     * @version 0.0.1
     */
    public static function setUpBeforeClass(): void
    {
        self::$manager = new ConditionManager;
    }

    /**
     * Evaluate condition
     *
     * Prepare raw condition for evaluation
     *
     * @param array $condition
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluate($conditions)
    {
        $context = new Context;
        $parsed  = ConditionParser::parse($conditions, $context);

        foreach ($parsed as &$group) {
            foreach ($group as $l => &$row) {
                if ($l !== 'Operator') {
                    $row = array(
                        // Left expression
                        'left' => ExpressionParser::convertedToValue(
                            $row['left'], $context
                        ),
                        // Right expression
                        'right' => (array) ExpressionParser::convertedToValue(
                            $row['right'], $context
                        )
                    );
                }
            }
        }

        return self::$manager->evaluate($parsed);
    }

}