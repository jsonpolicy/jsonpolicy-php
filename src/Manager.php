<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy;

use JsonPolicy\Core\Parser;

/**
 * Main policy manager
 *
 * @version 0.0.1
 */
class Manager
{

    /**
     * Debug mode
     *
     * @version 0.0.1
     */
    const DEBUG_MODE = 'debug';

    /**
     * Production mode (default)
     *
     * @version 0.0.1
     */
    const PROD_MODE = 'prod';

    /**
     * Single instance of itself
     *
     * @var JsonPolicy\Manager
     *
     * @access private
     * @version 0.0.1
     */
    private static $_instance = null;

    /**
     * Effect stemming map
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_stemming = array(
        'allowed' => 'allow',
        'denied'  => 'deny',
    );

    /**
     * Current identity
     *
     * @var object
     *
     * @access private
     * @version 0.0.1
     */
    private $_identity;

    /**
     * Policy manager settings
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_settings = [];

    /**
     * Policy parser
     *
     * @var Core\Parser
     *
     * @access private
     * @version 0.0.1
     */
    private $_parser;

    /**
     * Debug logs
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_logs = [];

    /**
     * Bootstrap constructor
     *
     * Initialize the JSON policy framework.
     *
     * @param array $settings
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected function __construct(array $settings = [])
    {
        if (empty($settings['identity'])) {
            $this->_identity = (object) [];
        } elseif (is_a($settings['identity'], 'Closure')) {
            $this->_identity = call_user_func($settings['identity'], $settings);
        } else {
            $this->_identity = $settings['identity'];
        }

        $this->_settings = $settings;

        // If there are any additional stemming pairs, merge them with the default
        if (isset($settings['effect_stemming']) && is_iterable($settings['effect_stemming'])) {
            $this->_stemming = array_merge(
                $this->_stemming,
                $settings['effect_stemming']
            );
        }
    }

    /**
     * Evaluate unknown method
     *
     * Tries to process methods like isAllowed or isDeniedTo. This method recognizes
     * and executes the following methods /^(is)([a-z]+)(To)?$/
     *
     * @param string $name
     * @param array  $args
     *
     * @return boolean|null
     *
     * @access public
     * @version 0.0.1
     */
    public function __call($name, $args)
    {
        $result = null;

        $this->startLog("Invoked \"{$name}\" method with params", $args);

        // We are calling method like isAllowed, isAttached or isDeniedTo
        if (strpos($name, 'is') === 0) {
            $resource = array_shift($args);
            $action   = array_shift($args);
            $params   = array_shift($args);

            if (strpos($name, 'To') === (strlen($name) - 2)) {
                $effect = substr($name, 2, -2);
            } else {
                $effect = substr($name, 2);
            }

            $result = $this->is(
                $resource, $this->_stemEffect($effect), $action, $params
            );
        } else {
            $this->log('Error: Unsupported method. It should start with "is".');
        }

        return $result;
    }

    /**
     * Get policy manager settings
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getSetting($name, $as_iterable = true)
    {
        $setting = null;

        if ($as_iterable) {
            $setting = $this->_getSettingIterator($name);
        } else if (isset($this->_settings[$name])) {
            $setting = $this->_settings[$name];
        }

        return $setting;
    }

    /**
     * Get current identity
     *
     * @return object
     *
     * @access public
     * @version 0.0.1
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Get policy param
     *
     * @param mixed $key
     * @param mixed $args
     *
     * @return mixed
     *
     * @access public
     * @version 0.0.1
     */
    public function getParam($key, $args = [])
    {
        $this->startLog("Fetching \"{$key}\" param");

        return $this->_parser->getParam($key, array(
            'Manager'  => $this,
            'Args'     => $args
        ));
    }

    /**
     * Wiping previous logs and insert the first log
     *
     * @param string $msg
     * @param mixed  $args
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function startLog($msg, $args = null)
    {
        $this->_logs = [];
        $this->log($msg, $args);
    }

    /**
     * Store log to the debugging log
     *
     * If manage runs in the "debug" mode, persist that log
     *
     * @param string $msg
     * @param mixed  $args
     *
     * @return void
     *
     * @access public
     * @version 0.0.1
     */
    public function log($msg, $args = null)
    {
        $mode = $this->getSetting('mode', false);

        if ($mode === self::DEBUG_MODE) {
            if (is_null($args)) {
                $this->_logs[] = $msg;
            } else {
                $this->_logs[] = array(
                    'msg'  => $msg,
                    'args' => $args
                );
            }
        }
    }

    /**
     * Get all logs
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getLogs()
    {
        return $this->_logs;
    }

    /**
     * Check if resource and/or action is allowed
     *
     * @param mixed  $resource Resource name or resource object
     * @param string $effect   Constraint effect (e.g. allow, deny)
     * @param string $action   Any specific action upon provided resource
     * @param mixed  $args     Inline arguments that are added to the context
     *
     * @return boolean|null The `null` is returned if there is no applicable statements
     *                      that explicitly define effect
     *
     * @access protected
     * @version 0.0.1
     */
    protected function is($resource, $effect, $action, $args)
    {
        $result = null;

        // Get resource alias
        $alias = $this->getResourceName($resource);

        // Prepare the context
        $context = array(
            'Manager'  => $this,
            'Resource' => $resource,
            'Alias'    => $alias,
            'Args'     => $args
        );

        // Log the context
        $this->log('Resource alias', $alias);
        $this->log('Effect', $effect);

        $xpath    = $alias . (is_null($action) ? '' : "::{$action}");
        $wildcard = "{$alias}::*";

        if ($this->_parser->isDefined($xpath)) {
            $result = $this->_parser->is($xpath, $effect, $context);
        } elseif ($this->_parser->isDefined($wildcard)) {
            $result = $this->_parser->is($wildcard, $effect, $context);
        }

        return $result;
    }

    /**
     * Initialize the policy manager
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected function initialize()
    {
        $this->_parser = new Parser($this->_getSettingIterator('repository'), $this);
    }

    /**
     * Convert resource to its alias name
     *
     * @param mixed $resource
     *
     * @return string|null
     *
     * @access protected
     * @version 0.0.1
     */
    protected function getResourceName($resource)
    {
        $name = null;

        foreach($this->_getSettingIterator('resources') as $callback) {
            $name = call_user_func($callback, $name, $resource, $this);
        }

        if (is_null($name) && is_object($resource)) {
            $name = get_class($resource);
        }

        return $name;
    }

    /**
     * Get setting's iterator
     *
     * The idea is that some settings (e.g. `repository` or `markers`) that are
     * passed to the Manager, contain iterable collection. In case, certain setting
     * is not explicitly defined or is not an iterable value, then return just empty
     * array
     *
     * @param string $name Setting name
     *
     * @return array|Traversable
     *
     * @access private
     * @version 0.0.1
     */
    private function _getSettingIterator($name)
    {
        $iterator = null;

        if (isset($this->_settings[$name])) {
            $setting = $this->_settings[$name];

            if (is_a($setting, 'Closure')) {
                $iterator = call_user_func($setting, $this);
            } else {
                $iterator = $setting;
            }
        }

        if (is_null($iterator) || !is_iterable($iterator)) {
            $iterator = [];
        }

        return $iterator;
    }

    /**
     * Stem the effect
     *
     * Basically try to stem the effect from something like "Allowed" to "allow", or
     * "Denied" to "deny".
     *
     * @param string $effect
     *
     * @return string
     *
     * @access private
     * @version 0.0.1
     */
    private function _stemEffect($effect)
    {
        $n = strtolower($effect);

        return (isset($this->_stemming[$n]) ? $this->_stemming[$n] : $n);
    }

    /**
     * Bootstrap the framework
     *
     * @param array   $options Manager options
     * @param boolean $force   Force to reinit if already initialized
     *
     * @return JsonPolicy\Manager
     *
     * @access public
     * @static
     * @version 0.0.1
     */
    public static function bootstrap(array $options = [], bool $force = false): Manager
    {
        if (is_null(self::$_instance) || $force) {
            self::$_instance = new self($options);

            // Initialize the repository and policies that are applicable to the
            // current identity
            self::$_instance->initialize();
        }

        return self::$_instance;
    }

}