<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy;

use JsonPolicy\Contract\IIdentity;
use JsonPolicy\Core\Parser,
    JsonPolicy\Identity\AnonymousIdentity;

class Manager
{

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
     * @var Contract\IIdentity
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
            $this->_identity = new AnonymousIdentity;
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
     * @throws Error
     * @version 0.0.1
     */
    public function __call($name, $args)
    {
        $result = null;

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
            throw new \Error("Unsupported method {$name}");
        }

        return $result;
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
     * @access public
     * @version 0.0.1
     */
    public function is($resource, $effect, $action, $args)
    {
        $result = null;

        // Get resource alias
        $alias = $this->getResourceName($resource);

        // Prepare the context
        $context = array(
            'Manager'  => $this,
            'Resource' => $resource,
            'Args'     => $args
        );

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
        }

        return $setting;
    }

    /**
     * Get current identity
     *
     * @return IIdentity
     *
     * @access public
     * @version 0.0.1
     */
    public function getIdentity(): IIdentity
    {
        return $this->_identity;
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
        $attached_policies = [];

        // Get current identity
        $identity = $this->getIdentity();

        // Iterate over each policy in the repository and if attached to current
        // identity, then pass it to the Core\Parser object for further processing
        foreach($this->_getSettingIterator('repository') as $id => $policy) {
            if (in_array($id, $identity->getAttachedPolicyIds(), true)) {
                $attached_policies[] = $policy;
            }

            if (array_key_exists('Assignee', $policy)) {
                if (in_array($identity->getType(), $policy['Assignee'], true)) {
                    $attached_policies[] = $policy;
                }
            }
        }

        $this->_parser = new Parser($attached_policies, $this);
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