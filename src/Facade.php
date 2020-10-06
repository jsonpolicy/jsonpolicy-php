<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy;

use JsonPolicy\Core\Manager,
    JsonPolicy\Identity\AnonymousIdentity;

class Facade
{

    /**
     * Single instance of itself
     *
     * @var JsonPolicy\Facade
     *
     * @access private
     * @version 0.0.1
     */
    private static $_instance = null;

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
     * Policy repository
     *
     * @var array|Closure
     *
     * @access private
     * @version 0.0.1
     */
    private $_repository = [];

    /**
     * Collection of resources
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_resources = [];

    /**
     * Collection of markers
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_markers = [];

    /**
     * Policy manager
     *
     * @var Core\Manager
     *
     * @access private
     * @version 0.0.1
     */
    private $_manager;

    /**
     * Bootstrap constructor
     *
     * Initialize the JSON policy framework.
     *
     * @param array $args
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected function __construct(array $args)
    {
        if (empty($args['identity'])) {
            $this->_identity = new AnonymousIdentity;
        } elseif (is_a($args['identity'], 'Closure')) {
            $this->_identity = call_user_func($args['identity'], $args);
        } else {
            $this->_identity = $args['identity'];
        }

        $this->_repository = (empty($args['repository']) ? [] : $args['repository']);
        $this->_markers    = (empty($args['markers']) ? [] : $args['markers']);
        $this->_resources  = (empty($args['resources']) ? [] : $args['resources']);
    }

    /**
     * Initialize the facade and policy manager
     *
     * @return void
     *
     * @access protected
     * @version 0.0.1
     */
    protected function initialize()
    {
        if (is_a($this->_repository, 'Closure')) {
            $policies = call_user_func($this->_repository, $this);
        } else {
            $policies = $this->_repository;
        }

        $attached = [];

        foreach($policies as $id => $policy) {
            if (in_array($id, $this->_identity->getAttachedPolicyIds(), true)) {
                $attached[] = $policy;
            } else if (array_key_exists('Assignee', $policy)) {
                if (in_array($this->_identity->getType(), $policy['Assignee'], true)) {
                    $attached[] = $policy;
                }
            }
        }

        $this->_manager = new Manager($this, $attached);
    }

    /**
     * Convert resource to its alias name
     *
     * Depending on the provided resource in the `isAllowed` method, convert the
     * resource to its alias name that is used in policies.
     *
     * @param mixed $resource
     *
     * @return string|null
     *
     * @access protected
     * @version 0.0.1
     */
    protected function convertObjectToResourceName($resource)
    {
        $name = null;

        if (is_array($this->_resources)) {
            foreach($this->_resources as $callback) {
                $name = call_user_func($callback, $name, $resource, $this);
            }
        }

        if (is_null($name) && is_object($resource)) {
            $name = get_class($resource);
        }

        return $name;
    }

    /**
     * Check if resource and/or action is allowed
     *
     * @param mixed  $resource Resource name or object
     * @param string $action   Any specific action upon provided resource
     *
     * @return boolean|null The `null` is returned if there is no applicable statements
     *                      that explicitly define effect
     *
     * @access public
     * @version 0.0.1
     */
    public function isAllowed($resource, $action = '*')
    {
        $allowed       = null;
        $resource_name = $this->convertObjectToResourceName($resource);

        if (!is_null($resource_name)) {
            $xpath    = "{$resource_name}::{$action}";
            $wildcard = "{$resource_name}::*";

            // Prepare the context
            $context = array(
                'facade'       => $this,
                'resource'     => $resource,
                'resourceName' => $resource_name,
                'action'       => $action
            );

            if ($this->_manager->hasResource($xpath)) {
                $allowed = $this->_manager->isAllowed($xpath, $context);
            } elseif ($this->_manager->hasResource($wildcard)) {
                $allowed = $this->_manager->isAllowed($wildcard, $context);
            }
        }

        return $allowed;
    }

    /**
     * Bootstrap the framework
     *
     * @param array   $options Facade options
     * @param boolean $force   Force to reinit if already initialized
     *
     * @return JsonPolicy\Facade
     *
     * @access public
     * @static
     * @version 0.0.1
     */
    public static function bootstrap(array $options = [], bool $force = false): Facade
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