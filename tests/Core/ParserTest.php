<?php

namespace JSONPolicy\UnitTest\Core;

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    public function testPushAndPop()
    {
        $stack = [];
        $this->assertSame(0, count($stack));
    }

}