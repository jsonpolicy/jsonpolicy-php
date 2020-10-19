<?php

namespace JSONPolicy\UnitTest;

use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{

    public function testPushAndPop()
    {
        $stack = [];
        $this->assertSame(0, count($stack));
    }

}