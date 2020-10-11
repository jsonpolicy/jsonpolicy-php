<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace JsonPolicy\Identity;

use JsonPolicy\Contract\IIdentity;

/**
 * Anonymous identity class
 *
 * @version 0.0.1
 */
class AnonymousIdentity implements IIdentity
{

    /**
     * Identity type
     *
     * This is just a basic anonymous identity
     *
     * @version 0.0.1
     */
    const TYPE = 'anonymous';

    /**
     * @inheritdoc
     */
    public function getAttachedPolicyIds(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return self::TYPE;
    }

}