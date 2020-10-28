<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy;

use JsonPolicy\Core\Context,
    JsonPolicy\Parser\PolicyParser,
    JsonPolicy\Manager\MarkerManager,
    JsonPolicy\Parser\ExpressionParser,
    JsonPolicy\Manager\TypecastManager,
    JsonPolicy\Manager\ConditionManager;

/**
 * Main policy manager
 *
 * @version 0.0.1
 */
class Manager
{

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
     * Policy manager settings
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_settings = [];

    /**
     * Marker manager
     *
     * @var JsonPolicy\Manager\MarkerManager
     *
     * @access private
     * @version 0.0.1
     */
    private $_marker_manager = null;

    /**
     * Typecast manager
     *
     * @var JsonPolicy\Manager\TypecastManager
     *
     * @access private
     * @version 0.0.1
     */
    private $_typecast_manager = null;

    /**
     * Condition manager
     *
     * @var JsonPolicy\Manager\ConditionManager
     *
     * @access private
     * @version 0.0.1
     */
    private $_condition_manager = null;

    /**
     * Parsed policy tree
     *
     * @var array
     *
     * @access protected
     * @version 0.0.1
     */
    private $_tree = [];

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
    protected function __construct(array $settings)
    {
        $this->_settings = $settings;
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

        // We are calling method like isAllowed, isAttached or isDeniedTo
        if (strpos($name, 'is') === 0) {
            $resource = array_shift($args);

            if (strpos($name, 'To') === (strlen($name) - 2)) {
                $effect = substr($name, 2, -2);
                $action = array_shift($args);
            } else {
                $effect = substr($name, 2);
            }

            $context_args = array_shift($args);

            $result = $this->is(
                $resource, $this->_stemEffect($effect), $action, $context_args
            );
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
        $result = null;

        if (isset($this->_tree['Param'][$key])) {
            $param = $this->getBestCandidate(
                $this->_tree['Param'][$key],
                $this->getContext([
                    'args' => $args
                ])
            );

            if (!is_null($param)) {
                $result = $param['Value'];
            }
        }

        return $result;
    }

    /**
     * Get marker manager
     *
     * @return JsonPolicy\Manager\MarkerManager
     *
     * @access public
     * @version 0.0.1
     */
    public function getMarkerManager()
    {
        if (is_null($this->_marker_manager)) {
            $this->_marker_manager = new MarkerManager(
                $this->getSetting('custom_markers')
            );
        }

        return $this->_marker_manager;
    }

    /**
     * Get typecast manager
     *
     * @return JsonPolicy\Manager\TypecastManager
     *
     * @access public
     * @version 0.0.1
     */
    public function getTypecastManager()
    {
        if (is_null($this->_typecast_manager)) {
            $this->_typecast_manager = new TypecastManager(
                $this->getSetting('custom_types')
            );
        }

        return $this->_typecast_manager;
    }

    /**
     * Get condition manager
     *
     * @return JsonPolicy\Manager\ConditionManager
     *
     * @access public
     * @version 0.0.1
     */
    public function getConditionManager()
    {
        if (is_null($this->_condition_manager)) {
            $this->_condition_manager = new ConditionManager(
                $this->getSetting('custom_conditions')
            );
        }

        return $this->_condition_manager;
    }

    /**
     * Get context
     *
     * @return Context
     *
     * @access public
     * @version 0.0.1
     */
    public function getContext(array $properties = [])
    {
        // If no args provided explicitly, then fallback to the default context
        // that can be defined during Policy Manager initialization
        if (empty($properties['args'])) {
            unset($properties['args']);
        }

        return new Context(array_merge(
            [ 'manager' => $this ],
            $this->getSetting('context'),
            $properties
        ));
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
        $res_name = $this->getResourceName($resource);

        // Prepare the context
        $context = $this->getContext([
            'resource' => $resource,
            'args'     => $args
        ]);

        $res_id   = $res_name . (is_null($action) ? '' : "::{$action}");
        $wildcard = "{$res_name}::*";

        if ($this->_tree['Statement'][$res_id]) {
            $stm = $this->getBestCandidate(
                $this->_tree['Statement'][$res_id], $context
            );
        } elseif ($this->_tree['Statement'][$wildcard]) {
            $stm = $this->getBestCandidate(
                $this->_tree['Statement'][$wildcard], $context
            );
        } elseif ($this->_tree['Statement']['*::*']) {
            $stm = $this->getBestCandidate(
                $this->_tree['Statement']['*::*'], $context
            );
        }

        if (!is_null($stm)) {
            $result = ($stm['Effect'] === $effect);
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
        // If there are any additional stemming pairs, merge them with the default
        $this->_stemming = array_merge(
            $this->_stemming,
            $this->getSetting('effect_stems')
        );

        // Parse the collection of policies
        $this->_tree = PolicyParser::parse(
            $this->getSetting('policies'),
            $this->getContext()
        );
    }

    /**
     * Based on multiple competing statements/params, get the best candidate
     *
     * @param array   $match
     * @param Context $context
     *
     * @return array|null
     *
     * @access protected
     * @version 0.0.1
     */
    protected function getBestCandidate($match, Context $context)
    {
        $candidate = null;

        if (is_array($match) && isset($match[0])) {
            // Take in consideration ONLY currently applicable statements or param
            // and select either the last one or the one that is enforced
            $enforced = false;

            foreach($match as $stm) {
                if ($this->_isApplicable($stm, $context)) {
                    if (!empty($stm['Enforce'])) {
                        $candidate = $stm;
                        $enforced  = true;
                    } elseif ($enforced === false) {
                        $candidate = $stm;
                    }
                }
            }
        } else if ($this->_isApplicable($match, $context)) {
            $candidate = $match;
        }

        return $candidate;
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

        foreach($this->getSetting('custom_resources') as $callback) {
            $name = call_user_func($callback, $name, $resource, $this);
        }

        if (is_null($name) && is_object($resource)) {
            $name = get_class($resource);
        }

        return $name;
    }

    /**
     * Check if policy statement or param is applicable
     *
     * @param array   $obj
     * @param Context $context
     *
     * @return boolean
     *
     * @access private
     * @version 0.0.1
     */
    private function _isApplicable($obj, Context $context)
    {
        $result = true;

        if (!empty($obj['Condition']) && is_iterable($obj['Condition'])) {
            $conditions = $obj['Condition'];

            foreach ($conditions as $i => &$group) {
                if ($i !== 'Operator') {
                    foreach ($group as $j => &$row) {
                        if ($j !== 'Operator') {
                            $row = array(
                                // Left expression
                                'left' => ExpressionParser::convertToValue(
                                    $row['left'], $context
                                ),
                                // Right expression
                                'right' => (array)ExpressionParser::convertToValue(
                                    $row['right'], $context
                                )
                            );
                        }
                    }
                }
            }

            $result = $this->getConditionManager()->evaluate($conditions);
        }

        return $result;
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
     * @param array $options Manager options
     *
     * @return JsonPolicy\Manager
     *
     * @access public
     * @static
     * @version 0.0.1
     */
    public static function bootstrap(array $options = []): Manager
    {
        $instance = new self($options);

        // Initialize the repository and policies that are applicable to the
        // current identity
        $instance->initialize();

        return $instance;
    }

}