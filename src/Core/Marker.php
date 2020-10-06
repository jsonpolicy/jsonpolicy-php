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
     * Literal map token's type to the executable method that returns actual value
     *
     * @var array
     *
     * @access protected
     * @version 0.0.1
     */
    protected static $map = array(
        'IDENTITY'          => __CLASS__ . '::getIdentityValue',
        'DATETIME'          => __CLASS__ . '::getDatetime',
        'HTTP_GET'          => __CLASS__ . '::getQueryParam',
        'HTTP_POST'         => __CLASS__ . '::getPostParam',
        'HTTP_COOKIE'       => __CLASS__ . '::getCookieParam',
        'PHP_SERVER'        => __CLASS__ . '::getServerParam',
        'PHP_GLOBAL'        => __CLASS__ . '::getGlobalVariable',
        'RESOURCE'          => __CLASS__ . '::getResourceValue',
        'PHP_ENV'           => 'getenv',
        'PHP_CONST'         => __CLASS__ . '::getConstant'
    );

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
    public static function evaluate($part, array $tokens, array $context)
    {
        foreach ($tokens as $token) {
            $val  = self::getTokenValue($token, $context);
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
    public static function getTokenValue($token, $context = array())
    {
        $parts = explode('.', preg_replace('/^\$\{([^}]+)\}$/', '${1}', $token), 2);

        if (isset(self::$map[$parts[0]])) {
            $value = call_user_func(self::$map[$parts[0]], $parts[1], $context);
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Get USER's value
     *
     * @param string $prop
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getIdentityValue($prop)
    {
        return null;
    }

    /**
     * Get inline argument
     *
     * @param string $prop
     * @param array  $context
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getResourceValue($prop, $context)
    {
        $value = null;

        if (is_object($context['resource'])) {
            if (property_exists($context['resource'], $prop)) {
                $value = $context['resource']->{$prop};
            }
        } elseif (is_array($context['resource'])) {
            if (array_key_exists($prop, $context['resource'])) {
                $value = $context['resource'][$prop];
            }
        }

        return $value;
    }

    /**
     * Get a value for the defined constant
     *
     * @param string $const
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getConstant($const)
    {
        return (defined($const) ? constant($const) : null);
    }

    /**
     * Get access policy param
     *
     * @param string $param
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getParam($param)
    {
        return null;
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
     * Get global variable's value
     *
     * @param string $var
     *
     * @return mixed
     *
     * @access protected
     * @version 0.0.1
     */
    protected static function getGlobalVariable($var)
    {
        return (isset($GLOBALS[$var]) ? $GLOBALS[$var] : null);
    }

}