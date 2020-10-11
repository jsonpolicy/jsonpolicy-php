<?php

use JsonPolicy\Contract\IIdentity;

class User implements IIdentity
{
    public $id;
    public $username;

    public function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

}