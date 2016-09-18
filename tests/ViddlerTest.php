<?php

namespace Zenapply\Viddler\Tests;

use Zenapply\Viddler\Viddler;
use Viddler as ViddlerFacade;
use Illuminate\Http\UploadedFile;

class ViddlerTest extends TestCase
{
	public function setUp()
    {
    	
        parent::setUp();
        $this->migrate();
    }

    public function tearDown()
    {
        $this->migrateReset();
        parent::tearDown();
    }

    public function testItCreatesAnInstanceOfViddler()
    {
        $obj = new Viddler();
        $this->assertInstanceOf(Viddler::class,$obj);
    }

    public function testUploadingAVideo()
    {
    	$this->flushTestStorageDisks();

    	$obj = new Viddler();
    	$file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
    	$model = $obj->upload($file, "small.mp4");
    	$this->assertEquals(true, file_exists(__DIR__.'/tmp/finished/small.mp4'));
    }

    
}
