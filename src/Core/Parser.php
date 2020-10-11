<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

use JsonPolicy\Manager;

/**
 * Policy parser
 *
 * @version 0.0.1
 */
class Parser
{

    /**
     * Parent policy manager
     *
     * @var JsonPolicy\Manager
     *
     * @access private
     * @version 0.0.1
     */
    private $_manager;

    /**
     * Collection of parsers
     *
     * @var array
     *
     * @access private
     * @version 0.0.01
     */
    private $_parser = array(
        'marker'    => null,
        'typecast'  => null,
        'condition' => null
    );

    /**
     * Parsed policy tree
     *
     * @var array
     *
     * @access protected
     * @version 0.0.1
     */
    private $_tree = array(
        'Statement' => array(),
        'Param'     => array()
    );

    /**
     * Constructor
     *
     * @param array              $policies
     * @param JsonPolicy\Manager $manager
     *
     * @access protected
     *
     * @return void
     * @version 0.0.1
     */
    public function __construct(array $policies, Manager $manager)
    {
        $this->_manager = $manager;

        foreach ($policies as $policy) {
            $this->updatePolicyTree($this->parsePolicy($policy));
        }
    }

    /**
     * Get policy parameter
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     *
     * @access public
     * @version 0.0.1
     */
    public function getParam($name, $args = array())
    {
        $value = null;

        if (isset($this->_tree['Param'][$name])) {
            $param = $this->_tree['Param'][$name];

            if ($this->_isApplicable($param, $args)) {
                $value = $param['Value'];
            }
        }

        return $value;
    }

    /**
     * Check if specific resource is defined in policy(s)
     *
     * @param string $resource
     *
     * @return boolean
     *
     * @access public
     * @version 0.0.1
     */
    public function isDefined($resource)
    {
        return isset($this->_tree['Statement'][$resource]);
    }

    /**
     * Check if specified action is allowed for resource
     *
     * This method is working with "Statement" array.
     *
     * @param string $resource Resource name
     * @param string $effect   Constraint effect
     * @param array  $context  Evaluation context
     *
     * @return boolean|null
     *
     * @access public
     * @version 0.0.1
     */
    public function is($resource, $effect, $context)
    {
        $result = null;

        if ($this->isDefined($resource)) {
            $stm = $this->getCandidateStatement(
                $this->_tree['Statement'][$resource], $context
            );

            if (!is_null($stm)) {
                $result = ($stm['Effect'] === $effect);
            }
        }

        return $result;
    }

    /**
     * Get marker parser
     *
     * @return Marker
     *
     * @access public
     * @version 0.0.1
     */
    public function getMarkerParser()
    {
        if (is_null($this->_parser['marker'])) {
            $this->_parser['marker'] = new Marker(
                $this, $this->_manager->getSetting('markers')
            );
        }

        return $this->_parser['marker'];
    }

    /**
     * Get typecast parser
     *
     * @return Typecast
     *
     * @access public
     * @version 0.0.1
     */
    public function getTypecastParser()
    {
        if (is_null($this->_parser['typecast'])) {
            $this->_parser['typecast'] = new Typecast(
                $this, $this->_manager->getSetting('typecasts')
            );
        }

        return $this->_parser['typecast'];
    }

    /**
     * Get condition parser
     *
     * @return Condition
     *
     * @access public
     * @version 0.0.1
     */
    public function getConditionParser()
    {
        if (is_null($this->_parser['condition'])) {
            $this->_parser['condition'] = new Condition(
                $this, $this->_manager->getSetting('conditions')
            );
        }

        return $this->_parser['condition'];
    }

    /**
     * Based on multiple competing statements, get the best candidate
     *
     * @param array $statements
     * @param array $context
     *
     * @return array|null
     *
     * @access protected
     * @version 0.0.1
     */
    protected function getCandidateStatement($statements, array $context)
    {
        $candidate = null;

        if (is_array($statements) && isset($statements[0])) {
            // Take in consideration ONLY currently applicable statements and select
            // either the last statement or the one that is enforced
            $enforced = false;

            foreach($statements as $stm) {
                if ($this->_isApplicable($stm, $context)) {
                    if (!empty($stm['Enforce'])) {
                        $candidate = $stm;
                        $enforced  = true;
                    } elseif ($enforced === false) {
                        $candidate = $stm;
                    }
                }
            }
        } else if ($this->_isApplicable($statements, $context)) {
            $candidate = $statements;
        }

        return $candidate;
    }

    /**
     * Parse JSON policy and extract statements and params
     *
     * @param StdClass $policy
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function parsePolicy($policy)
    {
        return array(
            'Statement' => $this->_getArrayOfArrays($policy, 'Statement'),
            'Param'     => $this->_getArrayOfArrays($policy, 'Param'),
        );
    }

    /**
     * Get array of array for Statement and Param policy props
     *
     * @param array  $input
     * @param string $prop
     *
     * @return array
     *
     * @access private
     * @version 0.0.1
     */
    private function _getArrayOfArrays($input, $prop)
    {
        $response = array();

        // Parse Statements and determine if it is multi-dimensional
        if (array_key_exists($prop, $input)) {
            if (!isset($input[$prop][0]) || !is_array($input[$prop][0])) {
                $response = array($input[$prop]);
            } else {
                $response = $input[$prop];
            }
        }

        return $response;
    }

    /**
     * Extend tree with additional statements and params
     *
     * @param array $addition
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function updatePolicyTree($addition)
    {
        $stmts  = &$this->_tree['Statement'];
        $params = &$this->_tree['Param'];

        // Step #1. If there are any params, let's index them and insert into the list
        foreach ($addition['Param'] as $param) {
            if (!empty($param['Key'])) {
                $param['Value'] = $this->replaceTokens($param['Value'], true);

                foreach($this->evaluatePolicyKey($param['Key']) as $key) {
                    if (!isset($params[$key]) || empty($params[$key]['Enforce'])) {
                        $params[$key] = $param;
                    }
                }
            }
        }

        // Step #2. If there are any statements, let's index them by resource name
        // and insert into the list of statements
        foreach ($addition['Statement'] as $stm) {
            $resources = (isset($stm['Resource']) ? (array) $stm['Resource'] : array());
            $actions   = (isset($stm['Action']) ? (array) $stm['Action'] : array(''));

            foreach ($resources as $res) {
                foreach($this->evaluatePolicyKey($res) as $resource) {
                    foreach ($actions as $act) {
                        $id = $resource . (!empty($act) ? "::{$act}" : '::*');

                        if (isset($stmts[$id])) {
                            if (isset($stmts[$id][0])) {
                                $stmts[$id][] = $stm;
                            } else {
                                $stmts[$id] = array($stmts[$id], $stm);
                            }
                        } else {
                            $stmts[$id] = $stm;
                        }
                    }
                }
            }
        }
    }

    /**
     * Evaluate resource name or param key
     *
     * The resource or param key may have tokens that build dynamic keys. This method
     * covers 3 possible scenario:
     * - Map To "=>" - the token should return array of values that are mapped to the
     *                 key;
     * - Token       - returns scalar value;
     * - Raw Value   - returns as-is
     *
     * @param string $key
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluatePolicyKey($key)
    {
        $response = array();

        // Allow to build resource name or param key dynamically.
        if (preg_match('/^(.*)[\s]+(map to|=>)[\s]+(.*)$/i', $key, $match)) {
            $values = (array) $this->getMarkerParser()->getTokenValue($match[3]);

            // Create the map of resources/params and replace
            foreach($values as $value) {
                $response[] = sprintf($match[1], $value);
            }
        } elseif (preg_match_all('/(\$\{[^}]+\})/', $key, $match)) {
            $tokens   = (is_iterable($match[1]) ? $match[1] : []);
            $response = array($this->getMarkerParser()->evaluate($key, $tokens));
        } else {
            $response = array($key);
        }

        return $response;
    }

    /**
     * Replace all the dynamic tokens recursively
     *
     * @param array   $data
     * @param boolean $type_cast
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function replaceTokens($data, $type_cast = false)
    {
        $replaced = array();

        if (is_scalar($data)) {
            $replaced = $this->_replaceTokensInString($data, $type_cast);
        } else {
            foreach($data as $key => $value) {
                // Evaluate array's key and replace tokens
                $key = $this->_replaceTokensInString($key);

                // Evaluate array's value and replace tokens
                if (is_array($value)) {
                    $replaced[$key] = $this->replaceTokens($value, $type_cast);
                } else {
                    $replaced[$key] = $this->_replaceTokensInString(
                        $value, $type_cast
                    );
                }
            }
        }

        return $replaced;
    }

    /**
     * Replace tokens is provided scalar string
     *
     * @param string  $token
     * @param boolean $type_cast
     *
     * @return mixed
     *
     * @access private
     * @version 0.0.1
     */
    private function _replaceTokensInString($token, $type_cast = false)
    {
        if (preg_match_all('/(\$\{[^}]+\})/', $token, $match)) {
            $value = $this->getMarkerParser()->evaluate($token, $match[1]);

            if ($type_cast === true) {
                $replaced = $this->getTypecastParser()->cast($value);
            } else {
                $replaced = $value;
            }
        } else {
            $replaced = $token;
        }

        return  $replaced;
    }

    /**
     * Check if policy statement is applicable
     *
     * @param array $stmt
     * @param array $args
     *
     * @return boolean
     *
     * @access private
     * @version 0.0.1
     */
    private function _isApplicable($stmt, array $context)
    {
        $result = true;

        if (!empty($stmt['Condition']) && is_array($stmt['Condition'])) {
            $result = $this->getConditionParser()->evaluate(
                $stmt['Condition'], $context
            );
        }

        return $result;
    }

}