<?php

namespace JSONPolicy\UnitTest;

use JsonPolicy\Manager,
    PHPUnit\Framework\TestCase,
    JsonPolicy\Manager\MarkerManager;

/**
 * Main manager
 *
 * @version 0.0.1
 */
class ManagerTest extends TestCase
{

    /**
     * Test that custom stem is registered and interpreted correctly
     *
     * {
     *      "Statement": {
     *          "Effect": "restrict",
     *          "Resource": "Application"
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testCustomStem()
    {
        $manager = Manager::bootstrap([
            'effect_stems' => [
                'restricted' => 'restrict'
            ],
            'custom_resources' => [
                function($name, $resource) {
                    if (is_null($name) && ($resource === 'Test')) {
                        $name = 'Application';
                    }

                    return $name;
                }
            ],
            'repository' => [
                '{"Statement":{"Effect":"restrict","Resource":"Application"}}'
            ]
        ]);

        $this->assertTrue($manager->isRestricted('Test'));
    }

    /**
     * Test that custom marker is registered and interpreted correctly
     *
     * {
     *      "Statement": {
     *          "Effect": "deny",
     *          "Resource": "stdClass",
     *          "Action": "purchase",
     *          "Condition": {
     *              "Equals": {
     *                  "(*int)${USER.id}": 1
     *              }
     *          }
     *      }
     * }
     * @access public
     * @version 0.0.1
     */
    public function testCustomMarker()
    {
        $manager = Manager::bootstrap([
            'custom_markers' => [
                'USER' => function($xpath) {
                    return MarkerManager::getValueByXPath([
                        'id' => 1
                    ], $xpath);
                }
            ],
            'repository' => [
                '{"Statement":{"Effect":"deny","Resource":"stdClass","Action":"purchase","Condition":{"Equals":{"(*int)${USER.id}":1}}}}'
            ]
        ]);

        $this->assertTrue($manager->isDeniedTo((object)[], 'purchase'));
    }

    /**
     * Test multiple competing statements
     *
     * {
     *      "Statement": [
     *          {
     *              "Effect": "allow",
     *              "Enforce": true,
     *              "Resource": "stdClass"
     *          },
     *          {
     *              "Effect": "deny",
     *              "Resource": "stdClass"
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testCompetingStatements()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Statement":[{"Effect":"allow","Enforce":true,"Resource":"stdClass"},{"Effect":"deny","Resource":"stdClass"}]}'
            ]
        ]);

        $this->assertTrue($manager->isAllowed((object)[]));
    }

    /**
     * Test resource wildcard statement
     *
     * {
     *      "Statement": {
     *          "Effect": "deny",
     *          "Resource": "*"
     *      }
     * }
     * @access public
     * @version 0.0.1
     */
    public function testResourceWildcardStatement()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Statement":{"Effect":"deny","Resource":"*"}}'
            ]
        ]);

        $this->assertTrue($manager->isDenied((object)[]));
    }

    /**
     * Test action wildcard statement
     *
     * {
     *      "Statement": {
     *          "Effect": "deny",
     *          "Resource": "stdClass"
     *      }
     * }
     * @access public
     * @version 0.0.1
     */
    public function testActionWildcardStatement()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Statement":{"Effect":"deny","Resource":"stdClass"}}'
            ]
        ]);

        $this->assertTrue($manager->isDeniedTo((object)[], 'delete'));
    }

    /**
     * Test a simple param
     *
     * {
     *      "Param": {
     *          "Key": "testing",
     *          "Value": "hello"
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSimpleParam()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Param":{"Key":"testing","Value":"hello"}}'
            ]
        ]);

        $this->assertEquals('hello', $manager->getParam('testing'));
    }

    /**
     * Test a simple param
     *
     * {
     *      "Param": {
     *          "Key": "testing",
     *          "Value": "hello",
     *          "Condition": {
     *              "NotEquals": {
     *                  "${ARGS.testing}": "hello"
     *              }
     *          }
     *      }
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testSimpleConditionalParam()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Param":{"Key":"testing","Value":"hello","Condition":{"NotEquals":{"${ARGS.testing}":"hello"}}}}'
            ]
        ]);

        // The condition makes the param inapplicable
        $this->assertNull($manager->getParam('testing', [
            'testing' => 'hello'
        ]));

        // The condition makes the param applicable
        $this->assertEquals('hello', $manager->getParam('testing', [
            'testing' => 'blah'
        ]));
    }

    /**
     * Test two conditional params that are competing
     *
     * {
     *      "Param": [
     *          {
     *              "Key": "environment",
     *              "Value": "this is staging",
     *              "Condition": {
     *                  "Equals": {
     *                      "${ARGS.env}": "staging"
     *                  }
     *              }
     *          },
     *          {
     *              "Key": "environment",
     *              "Value": "this is production",
     *              "Condition": {
     *                  "Equals": {
     *                      "${ARGS.env}": "production"
     *                  }
     *              }
     *          }
     *      ]
     * }
     *
     * @access public
     * @version 0.0.1
     */
    public function testCompetingParams()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{"Param":[{"Key":"environment","Value":"this is staging","Condition":{"Equals":{"${ARGS.env}":"staging"}}},{"Key":"environment","Value":"this is production","Condition":{"Equals":{"${ARGS.env}":"production"}}}]}'
            ]
        ]);

        $this->assertEquals('this is staging', $manager->getParam('environment', [
            'env' => 'staging'
        ]));

        $this->assertEquals('this is production', $manager->getParam('environment', [
            'env' => 'production'
        ]));

        $this->assertNull($manager->getParam('environment', [
            'env' => 'local'
        ]));
    }

    /**
     * Asset get settings method
     *
     * @access public
     * @version 0.0.1
     */
    public function testGetSetting()
    {
        $manager = Manager::bootstrap([
            'repository' => [
                '{}'
            ],
            'custom_prop' => 'a'
        ]);

        $this->assertTrue(is_array($manager->getSetting('repository')));
        $this->assertTrue(is_array($manager->getSetting('custom_prop')));
        $this->assertEquals('a', $manager->getSetting('custom_prop', false));
        $this->assertNull($manager->getSetting('unknown', false));
        $this->assertCount(0, $manager->getSetting('unknown'));
    }

}