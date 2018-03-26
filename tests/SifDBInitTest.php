<?php
use PHPUnit\Framework\TestCase;

class SifDBInitTest extends TestCase
{
    public function testInit()
    {
        $arr1 = ['a' => 'b'];
        $arr2 = ['a' => $arr1];

        $this->assertEquals($arr1, $arr2);
    }
}
