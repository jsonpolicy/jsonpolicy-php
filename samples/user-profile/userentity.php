<?php

class UserEntity
{
    public $id;
    public $username;

    public function __construct(array $data)
    {
        $this->id       = $data['id'];
        $this->username = $data['username'];
    }

}