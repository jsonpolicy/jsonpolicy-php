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

class Marker
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
     * Literal map token's type to the executable method that returns actual value
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_map = array(
        'IDENTITY' => __CLASS__ . '::getIdentityValue',
        'DATETIME' => __CLASS__ . '::getDatetime',
        'ARGS'     => __CLASS__ . '::getContextArgValue',
        'ENV'      => __CLASS__ . '::getEnvVar',
    );

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
     * Evaluate collection of tokens and replace them with values
     *
     * @param string $part    String with tokens
     * @param array  $tokens  Extracted token
     * @param array  $context Context
     *
     * @return string
     *
     * @access public
     * @version 0.0.1
     */
    public function evaluate($part, array $tokens, array $context = [])
    {
        foreach ($tokens as $token) {
            $val  = $this->getTokenValue($token, $context);
            $part = str_replace(
                $token,
                (is_scalar($val) || is_null($val) ? $val : json_encode($val)),
                $part
            );
        }

        return $part;
    }

    /**
     * Get token value
     *
     * @param string $token
     * @param array  $context
     *
     * @return mixed
     *
     * @access public
     * @version 0.0.1
     */
    public function getTokenValue($token, array $context = [])
    {
        $parts = explode('.', preg_replace('/^\$\{([^}]+)\}$/', '${1}', $token), 2);

        if ($parts[0] === $context['Alias']) {
            $value = self::_getValueByXPath($context, 'Resource.' . $parts[1]);
        } elseif (isset($this->_map[$parts[0]])) {
            $value = call_user_func($this->_map[$parts[0]], $parts[1], $context);
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Get value from the identity object
     *
     * @param string $prop
     * @param array  $context
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getIdentityValue($prop, array $context)
    {
        return self::_getValueByXPath($context['Manager']->getIdentity(), $prop);
    }

    /**
     * Get value from the context args
     *
     * @param string $prop
     * @param array  $context
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getContextArgValue($prop, array $context)
    {
        return self::_getValueByXPath($context, 'Args.' . $prop);
    }

    /**
     * Get current datetime
     *
     * @param string $format
     *
     * @return string
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getDatetime($format)
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->format($format);
    }

    /**
     * Get environment value
     *
     * @param string $prop
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getEnvVar($prop)
    {
        return getenv($prop);
    }

    /**
     * Get value by xpath
     *
     * This method supports multiple different path
     *
     * @param mixed  $obj
     * @param string $xpath
     *
     * @return mixed
     *
     * @access private
     * @version 0.0.1
     */
    private static function _getValueByXPath($obj, $xpath)
    {
        $value = $obj;
        $path  = trim(
            str_replace(
                array('["', '[', '"]', ']', '..'), '.', $xpath
            ),
            '\s.'
        );

        foreach(explode('.', $path) as $l) {
            if (is_object($value)) {
                if (property_exists($value, $l)) {
                    $value = $value->{$l};
                } else {
                    $value = null;
                    break;
                }
            } else if (is_array($value)) {
                if (array_key_exists($l, $value)) {
                    $value = $value[$l];
                } else {
                    $value = null;
                    break;
                }
            }
        }

        return $value;
    }

}