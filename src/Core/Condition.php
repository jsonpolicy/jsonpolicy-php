<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

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
        'Regex'           => 'evaluateRegexConditions'
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
        $res = true;

        foreach ($conditions as $type => $condition) {
            if (isset($this->_map[$type])) {
                if (method_exists($this, $this->_map[$type])) {
                    $callback = [$this, $this->_map[$type]];
                } else {
                    $callback = $this->_map[$type];
                }

                // If specific condition type is array, then combine
                // them with AND operation
                if (isset($condition[0]) && is_array($condition[0])) {
                    foreach ($condition as $set) {
                        $res = $res && call_user_func($callback, $set, $context);
                    }
                } else {
                    $res = $res && call_user_func($callback, $condition, $context);
                }
            } else {
                $res = false;
            }
        }

        return $res;
    }

    /**
     * Evaluate group of BETWEEN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateBetweenConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            // Convert the right condition into the array of array to cover more
            // complex between conditions like [[0,8],[13,15]]
            if (is_array($cnd['right'][0])) {
                $right = $cnd['right'];
            } else {
                $right = array($cnd['right']);
            }
            foreach ($right as $subset) {
                $min = (is_array($subset) ? array_shift($subset) : $subset);
                $max = (is_array($subset) ? end($subset) : $subset);

                $result = $result || ($cnd['left'] >= $min && $cnd['left'] <= $max);
            }
        }

        return $result;
    }

    /**
     * Evaluate group of EQUALS conditions
     *
     * The values have to be identical
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateEqualsConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || ($condition['left'] === $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT EQUALs conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotEqualsConditions($conditions, array $context)
    {
        return !$this->evaluateEqualsConditions($conditions, $context);
    }

    /**
     * Evaluate group of GREATER THEN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || ($condition['left'] > $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS THEN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || ($condition['left'] < $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of GREATER OR EQUALS THEN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterOrEqualsConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || ($condition['left'] >= $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS OR EQUALS THEN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessOrEqualsConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || ($condition['left'] <= $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of IN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateInConditions($conditions, array $context)
    {
        $res = false;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            if (is_array($cnd['left'])) {
                $cl = count($cnd['left']);
                $cr = count($cnd['right']);
                $ci = count(array_intersect($cnd['left'], (array) $cnd['right']));

                $res = $res || (($cl === $cr) && ($ci === $cl));
            } else {
                $res = $res || in_array($cnd['left'], (array) $cnd['right'], true);
            }
        }

        return $res;
    }

    /**
     * Evaluate group of NOT IN conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotInConditions($conditions, array $context)
    {
        return !$this->evaluateInConditions($conditions, $context);
    }

    /**
     * Evaluate group of LIKE conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLikeConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $cnd) {
            foreach ((array) $cnd['right'] as $el) {
                $sub = str_replace(
                    array('\*', '@'), array('.*', '\\@'), preg_quote($el)
                );
                $result = $result || preg_match('@^' . $sub . '$@', $cnd['left']);
            }
        }

        return $result;
    }

    /**
     * Evaluate group of NOT LIKE conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotLikeConditions($conditions, array $context)
    {
        return !$this->evaluateLikeConditions($conditions, $context);
    }

    /**
     * Evaluate group of REGEX conditions
     *
     * @param array $conditions
     * @param array $context
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateRegexConditions($conditions, array $context)
    {
        $result = false;

        foreach ($this->prepareConditions($conditions, $context) as $condition) {
            $result = $result || preg_match($condition['right'], $condition['left']);
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
            foreach ($conditions as $left => $right) {
                $result[] = array(
                    'left'  => $this->parseExpression($left, $context),
                    'right' => $this->parseExpression($right, $context)
                );
            }
        }

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
     * @access protected
     * @version 0.0.1
     */
    public function parseExpression($exp, array $context)
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
                $value = $this->parseExpression($value, $context);
            }
        } elseif (is_null($exp) === false) {
            $exp = false;
        }

        return $exp;
    }

}