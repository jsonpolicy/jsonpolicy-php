<?php

use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{

    public function testPushAndPop()
    {
        $stack = [];
        $this->assertSame(0, count($stack));
    }

}