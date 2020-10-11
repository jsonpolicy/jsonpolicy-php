<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

use DateTime,
    DateTimeZone;

class Typecast
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
     * Collection of additional types
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_map = [];

    /**
     * Construct the marker parser
     *
     * @param Parser $parser Parent policy parser
     * @param array  $map    Collection of additional markers
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
     * Execute type casting
     *
     * @param string $expression
     *
     * @return mixed
     *
     * @access public
     * @version 0.0.1
     */
    public function cast($expression)
    {
        $regex = '/^\(\*([a-z\d\-_]+)\)(.*)/i';

        // Note! It make no sense to have multiple type casting for one expression
        // due to the fact that they all would have to be concatenated as a string

        // If there is type casting, perform it
        if (preg_match( $regex, $expression, $scale)) {
            $expression = $this->_typecast($scale[2], $scale[1]);
        }

        return $expression;
    }


    /**
     * Cast value to specific type
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    private function _typecast($value, $type)
    {
        switch ($type) {
            case 'string':
                $value = (string) $value;
                break;

            case 'ip':
                $value = inet_pton($value);
                break;

            case 'int':
                $value = (int) $value;
                break;

            case 'boolean':
            case 'bool':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'array':
                $value = json_decode($value, true);
                break;

            case 'null':
                $value = ($value === '' ? null : $value);
                break;

            case 'date':
                $value = new DateTime($value, new DateTimeZone('UTC'));
                break;

            default:
                if (isset($this->_map[$type])) {
                    if (is_callable($this->_map[$type])) {
                        $value = call_user_func($this->_map[$type], $value);
                    } else {
                        throw new \Error("The {$type} typecast is not callable");
                    }
                }
                break;
        }

        return $value;
    }

}