<?php

namespace Zenapply\Viddler\Tests;

use Zenapply\Viddler\Viddler;
use Viddler as ViddlerFacade;

class ViddlerTest extends TestCase
{
    public function testItCreatesAnInstanceOfViddler(){
        $obj = new Viddler();
        $this->assertInstanceOf(Viddler::class,$obj);
    }
}
