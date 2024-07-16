<?php

namespace Machine\Http\Tests;

use Machine\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $request = new Request();


    }

    public function testInitialize()
    {
        $request = new Request();
        $request->initialize($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE);
    }
}
