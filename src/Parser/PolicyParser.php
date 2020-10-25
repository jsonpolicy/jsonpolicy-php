<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Parser;

use JsonPolicy\Core\Context;

/**
 * Policy parser
 *
 * @version 0.0.1
 */
class PolicyParser
{

    /**
     * Parse policies
     *
     * @param array                   $policies
     * @param JsonPolicy\Core\Context $context
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public static function parse(array $policies, Context $context)
    {
        $tree = [];

        foreach ($policies as $policy) {
            self::indexPolicy([
                'Statement' => self::_getArrayOfArrays($policy, 'Statement'),
                'Param'     => self::_getArrayOfArrays($policy, 'Param')
            ], $tree, $context);
        }

        return $tree;
    }

    /**
     * Extend tree with additional statements and params
     *
     * @param array                   $policy
     * @param array                   &$tree
     * @param JsonPolicy\Core\Context $context
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function indexPolicy($policy, &$tree, Context $context)
    {
        // Step #1. If there are any params, let's index them and insert into the list
        foreach ($policy['Param'] as $param) {
            self::indexParam($param, $tree, $context);
        }

        // Step #2. If there are any statements, let's index them by resource name
        // and insert into the list of statements
        foreach ($policy['Statement'] as $statement) {
            self::indexStatement($statement, $tree, $context);
        }
    }

    /**
     * Index a single param
     *
     * @param array                   $param
     * @param array                   &$tree
     * @param JsonPolicy\Core\Context $context
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function indexParam(array $param, array &$tree, Context $context)
    {
        if (!empty($param['Key'])) {
            $param_key      = ExpressionParser::parseToValue($param['Key'], $context);
            $param['Value'] = ExpressionParser::parseToValue($param['Value'], $context);

            // Index the conditions
            if (!empty($param['Condition'])) {
                $param['Condition'] = ConditionParser::parse(
                    $param['Condition'], $context
                );
            }

            foreach((array) $param_key as $id) {
                self::_insertIntoPolicyTree(
                    'Param', $id, self::_trim($param, ['Key']), $tree
                );
            }
        }
    }

    /**
     * Index a single statement
     *
     * @param array                   $stmt
     * @param array                   &$tree
     * @param JsonPolicy\Core\Context $context
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function indexStatement(array $stmt, array &$tree, Context $context)
    {
        $resources = (isset($stmt['Resource']) ? (array) $stmt['Resource'] : []);
        $actions   = (isset($stmt['Action']) ? (array) $stmt['Action'] : ['']);

        // Index the conditions
        if (!empty($stmt['Condition'])) {
            $stmt['Condition'] = ConditionParser::parse($stmt['Condition'], $context);
        }

        foreach($resources as $resource) {
            $resource_key = ExpressionParser::parseToValue($resource, $context);

            foreach((array) $resource_key as $id) {
                foreach ($actions as $action) {
                    self::_insertIntoPolicyTree(
                        'Statement',
                        $id . (!empty($action) ? "::{$action}" : '::*'),
                        self::_trim($stmt, ['Resource', 'Action']),
                        $tree
                    );
                }
            }
        }
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
    private static function _getArrayOfArrays($input, $prop)
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
     * Insert either statement or param into the policy tree
     *
     * @param string $type
     * @param string $key
     * @param array  $data
     * @param array  &$tree
     *
     * @return void
     *
     * @access private
     * @version 0.0.1
     */
    private static function _insertIntoPolicyTree($type, $key, $data, &$tree)
    {
        $branch = &$tree[$type];

        if (isset($branch[$key])) {
            if (isset($branch[$key][0])) {
                $branch[$key][] = $data;
            } else {
                $branch[$key] = array($branch[$key], $data);
            }
        } else {
            $branch[$key] = $data;
        }
    }

    /**
     * Trim unwanted properties from block of data
     *
     * @param array $block
     * @param array $properties
     *
     * @return array
     *
     * @access private
     * @version 0.0.1
     */
    private static function _trim($block, $properties)
    {
        foreach($properties as $prop) {
            if (isset($block[$prop])) {
                unset($block[$prop]);
            }
        }

        return $block;
    }

}