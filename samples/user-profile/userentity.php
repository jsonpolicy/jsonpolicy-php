<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

 /**
  * User Entity
  */
class UserEntity
{

    /**
     * User ID
     *
     * @var int
     */
    public $id;

    /**
     * User name
     *
     * @var string
     */
    public $username;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id       = $data['id'];
        $this->username = $data['username'];
    }

}