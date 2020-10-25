<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

/**
 * A single entity
 *
 * @version 0.0.1
 */
class Entity
{
    protected $raw;
    protected $format;
    protected $typecast;
    protected $tokens = [];
    protected $is_embedded = false;

    public function __construct($raw, array $operand)
    {
        $this->raw = $raw;

        foreach($operand as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function convertToValue(Context $context)
    {
        $value = null;

        foreach($this->tokens as $id => $token) {
            $token_value = $this->getTokenValue($token, $context);

            if (!empty($this->is_embedded)) {
                $value = str_replace($id, $token_value, $value);
            } else {
                $value = $token_value;
            }
        }

        // Typecast value if specified
        if (!empty($this->typecast)) {
            $value = $context->manager->cast($value, $this->typecast);
        }

        // Finally, if this is mapped entity, then map all the scalar values in the
        // value to the defined format
        if (!empty($this->format)) {
            $response = [];

            foreach((array)$value as $t) {
                $response[] = sprintf($this->format, $t);
            }
        } else {
            $response = $value;
        }

        return $response;
    }

    public function toArray()
    {
        $response = [];

        foreach($this as $key => $value) {
            if (!empty($value) || $key === 'raw') {
                $response[$key] = $value;
            }
        }

        return $response;
    }

    protected function getTokenValue(array $token, Context $context)
    {
        $value = null;

        if (array_key_exists('value', $token)) {
            $value = $token['value'];
        } else if (array_key_exists('source', $token)) {
            $value = $context->manager->getMarkerValue(
                $token['source'], $token['xpath'], $context
            );
        }

        return $value;
    }

}