<?php

namespace JSONPolicy\UnitTest\Manager;

use PHPUnit\Framework\TestCase,
    JsonPolicy\Manager\TypecastManager;

class TypecastManagerTest extends TestCase
{

    /**
     * Typecast parser instance
     *
     * @var JsonPolicy\Core\Typecast
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
        self::$manager = new TypecastManager;
    }

    /**
     * Testing that type casting is working property
     *
     * @dataProvider getTypes
     * @version 0.0.1
     */
    public function testTypecasting($value, $type, $type_func, $expected_value)
    {
        $value = self::$manager->cast($value, $type);

        $this->assertTrue(call_user_func($type_func, $value));
        $this->assertSame($value, $expected_value);
    }

    /**
     * Assert that (*date) typecasting works properly
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function testDateTypecasting()
    {
        $value = self::$manager->cast('2020-10-10', 'date');

        $this->assertEquals('DateTime', get_class($value));
        $this->assertEquals('2020-10-10', $value->format('Y-m-d'));
        $this->assertEquals('UTC', $value->getTimezone()->getName());
    }

    /**
     * Get collection of typecasting paris
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getTypes()
    {
        return [
            ['Hello', 'string', 'is_string', 'Hello'],
            ['127.0.0.1', 'ip', 'is_string', inet_pton('127.0.0.1')],
            ['5', 'int', 'is_int', 5],
            ['5.2', 'float', 'is_float', 5.2],
            ['true', 'bool', 'is_bool', true],
            ['1', 'bool', 'is_bool', true],
            ['yes', 'bool', 'is_bool', true],
            ['false', 'bool', 'is_bool', false],
            ['0', 'bool', 'is_bool', false],
            ['true', 'bool', 'is_bool', true],
            ['1', 'bool', 'is_bool', true],
            ['yes', 'bool', 'is_bool', true],
            ['false', 'bool', 'is_bool', false],
            ['0', 'bool', 'is_bool', false],
            ['["hello", "world"]', 'json', 'is_array', ["hello", "world"]],
            ['hello', 'array', 'is_array', ["hello"]],
            ['', 'null', 'is_null', null],
            [null, 'null', 'is_null', null]
        ];
    }

}