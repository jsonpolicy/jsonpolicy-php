<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */


 /**
  * Member class
  */
class Member {

    /**
     * Constructor
     *
     * @param array $args
     *
     * @access public
     */
    public function __construct($args)
    {
        foreach($args as $key => $value) {
            $this->{$key} = $value;
        }
    }

}