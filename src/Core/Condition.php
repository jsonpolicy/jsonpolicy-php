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
     * Map between condition type and method that evaluates the
     * group of conditions
     *
     * @var array
     *
     * @access protected
     * @version 0.0.1
     */
    protected static $map = array(
        'between'         => 'evaluateBetweenConditions',
        'equals'          => 'evaluateEqualsConditions',
        'notequals'       => 'evaluateNotEqualsConditions',
        'greater'         => 'evaluateGreaterConditions',
        'less'            => 'evaluateLessConditions',
        'greaterorequals' => 'evaluateGreaterOrEqualsConditions',
        'lessorequals'    => 'evaluateLessOrEqualsConditions',
        'in'              => 'evaluateInConditions',
        'notin'           => 'evaluateNotInConditions',
        'like'            => 'evaluateLikeConditions',
        'notlike'         => 'evaluateNotLikeConditions',
        'regex'           => 'evaluateRegexConditions'
    );

    /**
     * Evaluate the group of conditions based on type
     *
     * @param array $conditions List of conditions
     * @param array $args       Inline args for evaluation
     *
     * @return boolean
     *
     * @access public
     * @version 0.0.1
     */
    public static function evaluate($conditions, $args = array())
    {
        $res = true;

        foreach ($conditions as $type => $condition) {
            $type = strtolower($type);

            if (isset(self::$map[$type])) {
                $callback = __CLASS__ . "::" . self::$map[$type];

                // If specific condition type is array, then combine
                // them with AND operation
                if (isset($condition[0]) && is_array($condition[0])) {
                    foreach ($condition as $set) {
                        $res = $res && call_user_func($callback, $set, $args);
                    }
                } else {
                    $res = $res && call_user_func($callback, $condition, $args);
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
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateBetweenConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $cnd) {
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
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateEqualsConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || ($condition['left'] === $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT EQUALs conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateNotEqualsConditions($conditions, $args)
    {
        return !self::evaluateEqualsConditions($conditions, $args);
    }

    /**
     * Evaluate group of GREATER THEN conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateGreaterConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || ($condition['left'] > $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS THEN conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateLessConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || ($condition['left'] < $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of GREATER OR EQUALS THEN conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateGreaterOrEqualsConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || ($condition['left'] >= $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS OR EQUALS THEN conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateLessOrEqualsConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || ($condition['left'] <= $condition['right']);
        }

        return $result;
    }

    /**
     * Evaluate group of IN conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateInConditions($conditions, $args)
    {
        $res = false;

        foreach (self::prepareConditions($conditions, $args) as $cnd) {
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
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateNotInConditions($conditions, $args)
    {
        return !self::evaluateInConditions($conditions, $args);
    }

    /**
     * Evaluate group of LIKE conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateLikeConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $cnd) {
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
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateNotLikeConditions($conditions, $args)
    {
        return !self::evaluateLikeConditions($conditions, $args);
    }

    /**
     * Evaluate group of REGEX conditions
     *
     * @param array $conditions
     * @param array $args
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function evaluateRegexConditions($conditions, $args)
    {
        $result = false;

        foreach (self::prepareConditions($conditions, $args) as $condition) {
            $result = $result || preg_match($condition['right'], $condition['left']);
        }

        return $result;
    }

    /**
     * Prepare conditions by replacing all defined tokens
     *
     * @param array $conditions
     * @param array $args
     *
     * @return array
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function prepareConditions($conditions, $args)
    {
        $result = array();

        if (is_array($conditions)) {
            foreach ($conditions as $left => $right) {
                $result[] = array(
                    'left'  => self::parseExpression($left, $args),
                    'right' => self::parseExpression($right, $args)
                );
            }
        }

        return $result;
    }

    /**
     * Parse condition and try to replace all defined tokens
     *
     * @param mixed $exp  Part of the condition (either left or right)
     * @param array $args Inline arguments
     *
     * @return mixed Prepared part of the condition or false on failure
     *
     * @access protected
     * @version 0.0.1
     */
    public static function parseExpression($exp, $args)
    {
        if (is_scalar($exp)) {
            if (preg_match_all('/(\$\{[^}]+\})/', $exp, $match)) {
                $exp = Marker::evaluate($exp, $match[1], $args);
            }

            // Perform type casting if necessary
            $exp = Typecast::execute($exp);
        } elseif (is_array($exp) || is_object($exp)) {
            foreach ($exp as &$value) {
                $value = self::parseExpression($value, $args);
            }
        } elseif (is_null($exp) === false) {
            $exp = false;
        }

        return $exp;
    }

}