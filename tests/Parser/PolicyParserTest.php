<?php

namespace JSONPolicy\UnitTest\Parser;

use JsonPolicy\Manager,
    JsonPolicy\Core\Context,
    PHPUnit\Framework\TestCase,
    JsonPolicy\Parser\PolicyParser;

class PolicyParserTest extends TestCase
{

    /**
     * Test a single "Statement" with one resource
     *
     * {
     *      "Statement": {
     *          "Effect": "allow",
     *          "Resource": "article"
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleStatementWithOneResource()
    {
        $policies = [
            json_decode('{"Statement":{"Effect":"allow","Resource":"article"}}', true)
        ];

        $this->assertSame([
            'Statement' => [
                'article::*' => [
                    'Effect' => 'allow'
                ]
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test single statement with one resource mapped to a few actions
     *
     * {
     *      "Statement": {
     *          "Effect": "deny",
     *          "Resource": "article",
     *          "Action": [
     *              "read",
     *              "write"
     *          ]
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleStatementWithOneResourceFewActions()
    {
        $policies = [
            json_decode('{"Statement":{"Effect":"deny","Resource":"article","Action":["read","write"]}}', true)
        ];

        $this->assertSame([
            'Statement' => [
                'article::read' => [
                    'Effect' => 'deny'
                ],
                'article::write' => [
                    'Effect' => 'deny'
                ]
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test the resource mapping functionality
     *
     * {
     *      "Statement": {
     *          "Effect": "deny",
     *          "Resource": "RecordId:%d => (*json)[1,2]",
     *          "Action": [
     *              "delete"
     *          ]
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleStatementWithResourceMapping()
    {
        $policies = [
            json_decode('{"Statement":{"Effect":"deny","Resource":"RecordId:%d => (*json)[1,2]","Action":["delete"]}}', true)
        ];

        $this->assertSame([
            'Statement' => [
                'RecordId:1::delete' => [
                    'Effect' => 'deny'
                ],
                'RecordId:2::delete' => [
                    'Effect' => 'deny'
                ]
            ]
        ], PolicyParser::parse($policies, new Context([
            'manager' => Manager::bootstrap([], true)
        ])));
    }

    /**
     * Test multiple statements parsing
     *
     * {
     *      "Statement": [
     *          {
     *              "Effect": "deny",
     *              "Resource": "Order:PO0901"
     *          },
     *          {
     *              "Effect": "deny",
     *              "Resource": "Order:PO0902"
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleSimpleStatements()
    {
        $policies = [
            json_decode('{"Statement":[{"Effect":"deny","Resource":"Order:PO0901"},{"Effect":"deny","Resource":"Order:PO0902"}]}', true)
        ];

        $this->assertSame([
            'Statement' => [
                'Order:PO0901::*' => [
                    'Effect' => 'deny'
                ],
                'Order:PO0902::*' => [
                    'Effect' => 'deny'
                ]
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test multiple statements that target the same resource
     *
     * {
     *      "Statement": [
     *          {
     *              "Effect": "deny",
     *              "Resource": "Order:PO0901"
     *          },
     *          {
     *              "Effect": "allow",
     *              "Enforce": true,
     *              "Resource": "Order:PO0901"
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleCompetingStatements()
    {
        $policies = [
            json_decode('{"Statement":[{"Effect":"deny","Resource":"Order:PO0901"},{"Effect":"allow","Enforce":true,"Resource":"Order:PO0901"}]}', true)
        ];

        $this->assertSame([
            'Statement' => [
                'Order:PO0901::*' => [
                    [
                        'Effect' => 'deny'
                    ],
                    [
                        'Effect'  => 'allow',
                        'Enforce' => true
                    ]
                ],
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test a single "Param"
     *
     * {
     *      "Param": {
     *          "Key": "unittest",
     *          "Value": "test"
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleParam()
    {
        $policies = [
            json_decode('{"Param":{"Key":"unittest","Value":"test"}}', true)
        ];

        $this->assertSame([
            'Param' => [
                'unittest' => [
                    'Value' => 'test'
                ]
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test the param mapping functionality
     *
     * {
     *      "Param": {
     *          "Key": "endpoint:%s => (*array)${ARGS.environments}",
     *          "Value": "test"
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSingleParamWithMapping()
    {
        $policies = [
            json_decode('{"Param":{"Key":"endpoint:%s => (*array)${ARGS.environments}","Value":"test"}}', true)
        ];

        $this->assertSame([
            'Param' => [
                'endpoint:staging' => [
                    'Value' => 'test'
                ],
                'endpoint:production' => [
                    'Value' => 'test'
                ]
            ]
        ], PolicyParser::parse($policies, new Context([
            'manager' => Manager::bootstrap([], true),
            'args'    => [
                'environments' => ['staging', 'production']
            ]
        ])));
    }

    /**
     * Test multiple params parsing
     *
     * {
     *      "Param": [
     *          {
     *              "Key": "setting-a",
     *              "Value": "a"
     *          },
     *          {
     *              "Key": "setting-b",
     *              "Value": "b"
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleSimpleParams()
    {
        $policies = [
            json_decode('{"Param":[{"Key":"setting-a","Value":"a"},{"Key":"setting-b","Value":"b"}]}', true)
        ];

        $this->assertSame([
            'Param' => [
                'setting-a' => [
                    'Value' => 'a'
                ],
                'setting-b' => [
                    'Value' => 'b'
                ]
            ]
        ], PolicyParser::parse($policies, new Context));
    }

    /**
     * Test multiple params that target the same key
     *
     * {
     *      "Param": [
     *          {
     *              "Key": "endpoint",
     *              "Value": "/testing"
     *          },
     *          {
     *              "Key": "endpoint",
     *              "Value": "/production"
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testMultipleCompetingParams()
    {
        $policies = [
            json_decode('{"Param":[{"Key":"endpoint","Value":"/testing"},{"Key":"endpoint","Value":"/production"}]}', true)
        ];

        $this->assertSame([
            'Param' => [
                'endpoint' => [
                    [
                        'Value' => '/testing'
                    ],
                    [
                        'Value'  => '/production'
                    ]
                ],
            ]
        ], PolicyParser::parse($policies, new Context));
    }

}