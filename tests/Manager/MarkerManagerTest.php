<?php

namespace JSONPolicy\UnitTest\Manager;

use JsonPolicy\Core\Context,
    PHPUnit\Framework\TestCase,
    JsonPolicy\Manager\MarkerManager;

class MarkerManagerTest extends TestCase
{

    /**
     * Marker parser instance
     *
     * @var JsonPolicy\Core\MarkerManager
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
        self::$manager = new MarkerManager([
            'UserEntity' => function($xpath, Context $context) {
                return "custom:{$xpath}";
            }
        ]);
    }

    /**
     * Test DATETIME marker resolution
     *
     * @access public
     * @version 0.0.1
     */
    public function testDateTimeMarker()
    {
        $this->assertEquals(
            date('D'), self::$manager->getValue('DATETIME', 'D', new Context)
        );

        $this->assertEquals(
            date('Y-m-d'), self::$manager->getValue('DATETIME', 'Y-m-d', new Context)
        );
    }

    /**
     * Test ARGS marker resolution
     *
     * @access public
     * @version 0.0.1
     */
    public function testArgsMarker()
    {
        $this->assertEquals(
            1, self::$manager->getValue('ARGS', 'test', new Context([
                'args' => ['test' => 1]
            ]))
        );
    }

    /**
     * Test ENV marker resolution
     *
     * @access public
     * @version 0.0.1
     */
    public function testEnvMarker()
    {
        putenv('UNITTEST=a');

        $this->assertEquals(
            'a', self::$manager->getValue('ENV', 'UNITTEST', new Context)
        );
    }

    /**
     * Test default fallback to resource property
     *
     * @access public
     * @version 0.0.1
     */
    public function testResourceMarker()
    {
        $this->assertEquals(
            1, self::$manager->getValue('Car', 'test', new Context([
                'resource' => (object) [
                    'test' => 1
                ]
            ]))
        );
    }

    /**
     * Test custom marker evaluation
     *
     * @access public
     * @version 0.0.1
     */
    public function testCustomMarker()
    {
        $this->assertEquals(
            'custom:some-prop',
            self::$manager->getValue('UserEntity', 'some-prop', new Context)
        );
    }

    /**
     * Test various combination of xpaths
     *
     * @dataProvider getXpathCollection
     *
     * @access public
     * @version 0.0.1
     */
    public function testMarkerXpath($xpath, $resource, $expected)
    {
        $this->assertEquals(
            $expected, self::$manager->getValue('Object', $xpath, new Context([
                'resource' => $resource
            ]))
        );
    }

    /**
     * Get collection of various xpath combination
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getXpathCollection()
    {
        return [
            ['test', ['test' => 'u'], 'u'],
            ['test[a]', ['test' => ['a' => 'b']], 'b'],
            ['test["a"]', ['test' => ['a' => 'b']], 'b'],
            ['test["a"]["b"]', ['test' => ['a' => ['b' => 'b']]], 'b'],
            ['test.a.b', ['test' => ['a' => (object)['b' => 'b']]], 'b'],
            ['test[a].v', (object)['test' => ['a' => ['v' => 'b']]], 'b'],
        ];
    }

}