<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

/**
 * Conditions parser and evaluator
 *
 * @version 0.0.1
 */
class Condition
{

    /**
     * Parent policy parser
     *
     * @var Parser
     *
     * @access private
     * @version 0.0.1
     */
    private $_parser;

    /**
     * Map between condition type and method that evaluates the
     * group of conditions
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_map = array(
        'Between'         => 'evaluateBetweenConditions',
        'Equals'          => 'evaluateEqualsConditions',
        'NotEquals'       => 'evaluateNotEqualsConditions',
        'Greater'         => 'evaluateGreaterConditions',
        'Less'            => 'evaluateLessConditions',
        'GreaterOrEquals' => 'evaluateGreaterOrEqualsConditions',
        'LessOrEquals'    => 'evaluateLessOrEqualsConditions',
        'In'              => 'evaluateInConditions',
        'NotIn'           => 'evaluateNotInConditions',
        'Like'            => 'evaluateLikeConditions',
        'NotLike'         => 'evaluateNotLikeConditions',
        'RegEx'           => 'evaluateRegexConditions'
    );

    /**
     * Construct the condition parser
     *
     * @param Parser $parser Parent policy parser
     * @param array  $map    Collection of additional conditions
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function __construct(Parser $parser, array $map = [])
    {
        $this->_parser = $parser;
        $this->_map    = array_merge($this->_map, $map);
    }

    /**
     * Evaluate the group of conditions based on type
     *
     * @param array $conditions List of conditions
     * @param array $context    Context
     *
     * @return boolean
     *
     * @access public
     * @version 0.0.1
     */
    public function evaluate($conditions, array $context)
    {
        $result   = null;
        $operator = $this->_determineConditionOperator($conditions);

        // Log
        $this->_parser->log(
            "Evaluating condition groups with {$operator} operator", $conditions
        );

        foreach ($conditions as $type => $group) {
            if (isset($this->_map[$type])) {
                if (method_exists($this, $this->_map[$type])) {
                    $callback = [$this, $this->_map[$type]];
                } else {
                    $callback = $this->_map[$type];
                }

                // Determining logical operator within group
                $group_operator = $this->_determineConditionOperator($group);

                $this->_parser->log(
                    "Evaluating {$type} group with {$group_operator} operator",
                    $group
                );

                // Evaluating group
                $group_res = call_user_func(
                    $callback, $group, $context, $group_operator
                );

                // Log result
                $this->_parser->log(
                    "Group {$type} evaluated as " . ($group_res ? 'TRUE' : 'FALSE')
                );

                $result = $this->_compute($result, $group_res, $operator);
            } else {
                $this->_parser->log("Warning: Unsupported condition type {$type}");

                $result = false;
            }
        }

        $this->_parser->log(
            'Conditions evaluated as ' . ($result ? 'TRUE' : 'FALSE')
        );

        return $result;
    }

    /**
     * Evaluate group of BETWEEN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateBetweenConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach ($cnd['right'] as $subset) {
                $min = (is_array($subset) ? array_shift($subset) : $subset);
                $max = (is_array($subset) ? end($subset) : $subset);

                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] >= $min && $cnd['left'] <= $max), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of EQUALS conditions
     *
     * The values have to be identical
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateEqualsConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] === $value), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT EQUALs conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotEqualsConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        return !$this->evaluateEqualsConditions($conditions, $context, $operator);
    }

    /**
     * Evaluate group of GREATER THEN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] > $value), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS THEN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] < $value), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of GREATER OR EQUALS THEN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterOrEqualsConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] >= $value), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS OR EQUALS THEN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessOrEqualsConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = $this->_compute(
                    $sub_result, ($cnd['left'] <= $value), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of IN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateInConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach ($cnd['right'] as $subset) {
                $sub_result = $this->_compute(
                    $sub_result, in_array($cnd['left'], $subset, true), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT IN conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotInConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        return !$this->evaluateInConditions($conditions, $context, $operator);
    }

    /**
     * Evaluate group of LIKE conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLikeConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub = str_replace(
                    array('\*', '#'), array('.*', '\\#'), preg_quote($value)
                );
                $sub_result = $this->_compute(
                    $sub_result, preg_match('#^' . $sub . '$#', $cnd['left']), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT LIKE conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotLikeConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        return !$this->evaluateLikeConditions($conditions, $context, $operator);
    }

    /**
     * Evaluate group of REGEX conditions
     *
     * @param array  $conditions
     * @param array  $context
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateRegexConditions(
        $conditions, array $context, $operator = 'AND'
    ) {
        $result = null;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $regex) {
                // Check if RegEx is wrapped with forward slashes "/" and if not,
                // wrap it
                if (strpos($regex, '/') !== 0) {
                    $regex = "/{$regex}/";
                }

                $sub_result = $this->_compute(
                    $sub_result, preg_match($regex, $cnd['left']), 'OR'
                );
            }

            $result = $this->_compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Prepare conditions by replacing all defined tokens
     *
     * @param array $conditions
     * @param array $context
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected function prepareConditions($conditions, array $context)
    {
        $result = array();

        if (is_array($conditions)) {
            foreach ($conditions as $l => $r) {
                $right_operand = $this->_parseExpression($r, $context);

                if (is_array($right_operand)) {
                    // Convert the right operand into the array of array to cover
                    // more complex conditions like [[0,8],[13,15]]
                    if (!is_array($right_operand[0])) {
                        $right_operand = array($right_operand);
                    }
                } else {
                    $right_operand = array($right_operand);
                }

                $result[] = array(
                    'left'  => $this->_parseExpression($l, $context),
                    'right' => $right_operand
                );
            }
        }

        $this->_parser->log('Prepared condition group', $result);

        return $result;
    }

    /**
     * Parse condition and try to replace all defined tokens
     *
     * @param mixed $exp     Part of the condition (either left or right)
     * @param array $context Context
     *
     * @return mixed Prepared part of the condition or false on failure
     *
     * @access private
     * @version 0.0.1
     */
    private function _parseExpression($exp, array $context)
    {
        if (is_scalar($exp)) {
            if (preg_match_all('/(\$\{[^}]+\})/', $exp, $match)) {
                $exp = $this->_parser->getMarkerParser()->evaluate(
                    $exp, $match[1], $context
                );
            }

            // Perform type casting if necessary
            $exp = $this->_parser->getTypecastParser()->cast($exp);
        } elseif (is_array($exp) || is_object($exp)) {
            foreach ($exp as &$value) {
                $value = $this->_parseExpression($value, $context);
            }
        } elseif (is_null($exp) === false) {
            $exp = false;
        }

        return $exp;
    }

    /**
     * Determine primary logical operator
     *
     * Based on the reserved "Operator" attribute, determine the how the
     * sub-conditions are going to be logically joined to determine boolean result
     *
     * @param array &$conditions
     *
     * @return string
     *
     * @access private
     * @version 0.0.1
     */
    private function _determineConditionOperator(array &$conditions)
    {
        $op = 'AND';

        if (isset($conditions['Operator'])) {
            $op = $conditions['Operator'];

            // Remove this reserved property to avoid it from being used as actual
            // condition
            unset($conditions['Operator']);
        }

        return (in_array($op, array('AND', 'OR', 'XOR'), true) ? $op : 'AND');
    }

    /**
     * Compute the logical expression
     *
     * @param boolean $left
     * @param boolean $right
     * @param string  $operator
     *
     * @return boolean|null
     *
     * @access private
     * @version 0.0.1
     */
    private function _compute($left, $right, $operator)
    {
        $result = null;

        if ($left === null) {
            $result = $right;
        } elseif ($operator === 'AND') {
            $result = $left && $right;
        } elseif ($operator === 'OR') {
            $result = $left || $right;
        } elseif ($operator === 'XOR') {
            $result = $left xor $right;
        }

        return $result;
    }

}