<?php

namespace JSONPolicy\UnitTest\Manager;

use JsonPolicy\Manager,
    JsonPolicy\Core\Entity,
    PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{

    /**
     * Assert proper entity initialization
     *
     * Making sure that all reserved properties are initialized correctly and unknown
     * properties are dropped
     *
     * @access public
     * @version 0.0.1
     */
    public function testInitialization()
    {
        $entity = new Entity('testing', [
            'format'   => 'unit:%s',
            'typecast' => 'string',
            'tokens'   => [
                [
                    'value' => 'a'
                ]
            ],
            'is_embedded' => true,
            'unsupported' => 'blah'
        ]);

        $this->assertSame([
                'raw'      => 'testing',
                'format'   => 'unit:%s',
                'typecast' => 'string',
                'tokens'   => [
                    [
                        'value' => 'a'
                    ]
                ],
                'is_embedded' => true,
            ],
            $entity->toArray()
        );
    }

    /**
     * Test a simple value conversion
     *
     * @access public
     * @version 0.0.1
     */
    public function testSimpleValueConversion()
    {
        $context = Manager::bootstrap([])->getContext();

        // Evaluate a string value
        $entity = new Entity('testing', [
            'tokens' => [
                [
                    'value' => 'a'
                ]
            ]
        ]);
        $this->assertEquals('a', $entity->convertToValue($context));

        // Evaluate a integer value
        $entity = new Entity('testing', [
            'tokens' => [
                [
                    'value' => 1
                ]
            ]
        ]);
        $this->assertEquals(1, $entity->convertToValue($context));

        // Evaluate a boolean value
        $entity = new Entity('testing', [
            'tokens' => [
                [
                    'value' => true
                ]
            ]
        ]);
        $this->assertEquals(true, $entity->convertToValue($context));
    }

    /**
     * Test a simple value conversion
     *
     * @access public
     * @version 0.0.1
     */
    public function testTypecastValueConversion()
    {
        $context = Manager::bootstrap([])->getContext();

        // Evaluate a string value
        $entity = new Entity('testing', [
            'typecast' => 'int',
            'tokens' => [
                [
                    'value' => '1'
                ]
            ]
        ]);
        $value = $entity->convertToValue($context);

        $this->assertEquals(1, $value);
        $this->assertIsInt($value);
    }

    /**
     * Test a simple value conversion
     *
     * @access public
     * @version 0.0.1
     */
    public function testMappingValueConversion()
    {
        $context = Manager::bootstrap([])->getContext();

        // Evaluate a string value
        $entity = new Entity('row:%d => (*json)[1,2]', [
            'typecast' => 'json',
            'format'   => 'row:%d',
            'tokens'   => [
                [
                    'value' => '[1,2]'
                ]
            ]
        ]);

        $this->assertEquals([
            'row:1',
            'row:2'
        ], $entity->convertToValue($context));
    }

    /**
     * Test a simple value conversion
     *
     * @access public
     * @version 0.0.1
     */
    public function testEmbeddedValueConversion()
    {
        $context = Manager::bootstrap([
            'context' => [
                'resource' => ['name' => 'John', 'dob' => '2000-01-01']
            ]
        ])->getContext();

        // Evaluate a single string value
        $entity = new Entity('Howdy, ${USER.name}', [
            'tokens' => [
                '${USER.name}' => [
                    'source' => 'USER',
                    'xpath'  => 'name'
                ]
            ],
            'is_embedded' => true
        ]);

        $this->assertEquals('Howdy, John', $entity->convertToValue($context));

        // Evaluate a few embedded string values
        $entity = new Entity('${USER.name} was born on ${USER.dob}', [
            'tokens' => [
                '${USER.name}' => [
                    'source' => 'USER',
                    'xpath'  => 'name'
                ],
                '${USER.dob}' => [
                    'source' => 'USER',
                    'xpath'  => 'dob'
                ]
            ],
            'is_embedded' => true
        ]);

        $this->assertEquals(
            'John was born on 2000-01-01', $entity->convertToValue($context)
        );
    }

    /**
     * Test marker value conversions
     *
     * @access public
     * @version 0.0.1
     */
    public function testMarkerValueConversion()
    {
        $context = Manager::bootstrap([
            'context' => [
                'resource' => ['name' => 'John', 'dob' => '2000-01-01']
            ]
        ])->getContext();

        // Evaluate a single string value
        $entity = new Entity('${USER.name}', [
            'tokens' => [
                '${USER.name}' => [
                    'source' => 'USER',
                    'xpath'  => 'name'
                ]
            ]
        ]);

        $this->assertEquals('John', $entity->convertToValue($context));

        // Evaluate a type casted value conversion
        $entity = new Entity('(*date)${USER.dob}', [
            'typecast' => 'date',
            'tokens'   => [
                '${USER.dob}' => [
                    'source' => 'USER',
                    'xpath'  => 'dob'
                ]
            ]
        ]);

        $date = $entity->convertToValue($context);

        $this->assertEquals('DateTime', get_class($date));
        $this->assertEquals('2000-01-01', $date->format('Y-m-d'));
    }

}