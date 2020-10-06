<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Contract;

interface IIdentity
{

    /**
     * Get array of attached policy IDs
     *
     * @return array
     *
     * @access public
     * @version 0.0.1
     */
    public function getAttachedPolicyIds(): array;

    /**
     * Get identity type
     *
     * @return string
     *
     * @access public
     * @version 0.0.1
     */
    public function getType(): string;

}