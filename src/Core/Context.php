<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Core;

/**
 * Context
 *
 * @version 0.0.1
 */
class Context
{

    public function __construct(array $context = [])
    {
        foreach($context as $key => $value) {
            $this->{$key} = $value;
        }
    }

}