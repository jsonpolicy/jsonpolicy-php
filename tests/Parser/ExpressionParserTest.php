<?php

namespace JSONPolicy\UnitTest\Parser;

use PHPUnit\Framework\TestCase,
    JsonPolicy\Parser\ExpressionParser;

class ExpressionParserTest extends TestCase
{

    /**
     * Test various simple scalar expressions
     *
     * @dataProvider getExpressionFeed
     * @version 0.0.1
     */
    public function testExpressionParser($expression, $expected)
    {
        $result = ExpressionParser::parse($expression);

        $this->assertSame($this->convertParsedResultToArray($result), $expected);
    }

    /**
     * Get various combination of expressions
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getExpressionFeed()
    {
        return [
            // A simple scalar integer value
            [5, [
                'raw' => 5,
                'tokens' => [
                    ['value' => 5]
                ]
            ]],
            // A simple scalar string value
            ['hello', [
                'raw' => 'hello',
                'tokens' => [
                    ['value' => 'hello']
                ]
            ]],
            // A simple scalar boolean value
            [true, [
                'raw' => true,
                'tokens' => [
                    ['value' => true]
                ]
            ]],
            // A simple scalar null value
            [null, [
                'raw' => null,
                'tokens' => [
                    ['value' => null]
                ]
            ]],
            // A single marker
            ['${UNITTEST.a}', [
                'raw' => '${UNITTEST.a}',
                'tokens' => [
                    '${UNITTEST.a}' => [
                        'source' => 'UNITTEST',
                        'xpath'  => 'a'
                    ]
                ]
            ]],
            // A single embedded marker
            ['${UNITTEST.a}-suffix', [
                'raw'    => '${UNITTEST.a}-suffix',
                'tokens' => [
                    '${UNITTEST.a}' => [
                        'source' => 'UNITTEST',
                        'xpath'  => 'a'
                    ]
                ],
                'is_embedded' => true
            ]],
            // A multiple embedded markers
            ['${UNITTEST.a}-${UNITTEST2.b}', [
                'raw'    => '${UNITTEST.a}-${UNITTEST2.b}',
                'tokens' => [
                    '${UNITTEST.a}' => [
                        'source' => 'UNITTEST',
                        'xpath'  => 'a'
                    ],
                    '${UNITTEST2.b}' => [
                        'source' => 'UNITTEST2',
                        'xpath'  => 'b'
                    ]
                ],
                'is_embedded' => true
            ]],
            // A simple array of scalar values
            [['a', 'b'], [
                [
                    'raw' => 'a',
                    'tokens' => [
                        ['value' => 'a']
                    ]
                ],
                [
                    'raw' => 'b',
                    'tokens' => [
                        ['value' => 'b']
                    ]
                ]
            ]],
            // A mixed array of scalar values and markers
            [['a', '${UNITTEST.b}'], [
                [
                    'raw' => 'a',
                    'tokens' => [
                        ['value' => 'a']
                    ]
                ],
                [
                    'raw' => '${UNITTEST.b}',
                    'tokens' => [
                        '${UNITTEST.b}' => [
                            'source' => 'UNITTEST',
                            'xpath'  => 'b'
                        ]
                    ]
                ]
            ]],
            // An array of array with mixed types of values and markers
            [[['a', '${UNITTEST.b}'], [1, 4]], [
                [
                    [
                        'raw' => 'a',
                        'tokens' => [
                            ['value' => 'a']
                        ]
                    ],
                    [
                        'raw' => '${UNITTEST.b}',
                        'tokens' => [
                            '${UNITTEST.b}' => [
                                'source' => 'UNITTEST',
                                'xpath'  => 'b'
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'raw' => 1,
                        'tokens' => [
                            ['value' => 1]
                        ]
                    ],
                    [
                        'raw' => 4,
                        'tokens' => [
                            ['value' => 4]
                        ]
                    ]
                ]
            ]],
            // Marker mapping with embedded array
            ['ResourceId:%d:prop => (*json)[1,2,3]', [
                'raw'      => 'ResourceId:%d:prop => (*json)[1,2,3]',
                'format'   => 'ResourceId:%d:prop',
                'typecast' => 'json',
                'tokens'   => [
                    [
                        'value' => '[1,2,3]'
                    ]
                ]
            ]],
            // Marker mapping with marker
            ['ResourceId:%d:prop => ${UNITTEST.array}', [
                'raw'      => 'ResourceId:%d:prop => ${UNITTEST.array}',
                'format'   => 'ResourceId:%d:prop',
                'tokens'   => [
                    '${UNITTEST.array}' => [
                        'source' => 'UNITTEST',
                        'xpath'  => 'array'
                    ]
                ]
            ]]
        ];
    }

    /**
     * Convert Entity or array of Entities into plain array
     *
     * @param JsonPolicy\Core\Entity|array $result
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function convertParsedResultToArray($result)
    {
        if (is_array($result)) {
            foreach($result as &$value) {
                $value = $this->convertParsedResultToArray($value);
            }
        } else {
            $result = $result->toArray();
        }

        return $result;
    }

}