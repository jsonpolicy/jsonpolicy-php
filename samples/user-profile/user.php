<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */


 /**
  * User class
  */
class User
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
     * @param int    $id
     * @param string $username
     */
    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

}