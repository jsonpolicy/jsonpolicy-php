<?php

namespace JSONPolicy\UnitTest\Core\Condition;

use JsonPolicy\Manager,
    JsonPolicy\Core\Parser,
    JsonPolicy\Core\Condition;

/**
 * Common setup for the conditions testing
 *
 * @version 0.0.1
 */
trait ConditionTrait
{

    /**
     * Condition parser instance
     *
     * @var JsonPolicy\Core\Condition
     *
     * @access protected
     * @version 0.0.1
     */
    protected static $condition;

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
        $manager = Manager::bootstrap([], true);
        $parser  = new Parser([], $manager);

        self::$condition = new Condition($parser);
    }

}