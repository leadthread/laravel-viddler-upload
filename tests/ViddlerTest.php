<?php

namespace Zenapply\Viddler\Tests;

use Zenapply\Viddler\Models\Video;
use Zenapply\Viddler\Viddler;
use Viddler as ViddlerFacade;
use Illuminate\Http\UploadedFile;
use Zenapply\Viddler\Exceptions\IncorrectVideoTypeException;

class ViddlerTest extends TestCase
{
	public function setUp()
    {
        parent::setUp();
    	$this->flushTestStorageDisks();
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

    public function testConvertingMovToMp4()
    {
        $obj = new Viddler();
        $file = new UploadedFile(__DIR__."/files/small.mov", "small.mov");
        $model = $obj->create($file, "Test");
        $model = Video::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/finished/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals(true, $model->isFinished());
    }

    public function testItFailsWhenUploadingANonVideoFile()
    {
        $this->setExpectedException(IncorrectVideoTypeException::class);
        $title = "This is a test video";
        $obj = new Viddler();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt");
        $model = $obj->create($file, $title);
    }

    public function testUploadingAVideo()
    {
        $title = "This is a test video";
        $obj = new Viddler();
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
        $model = $obj->create($file, $title);
        $model = Video::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/finished/'.$model->filename));
        $this->assertEquals($title, $model->title);
        $this->assertEquals(true, $model->isFinished());
    }
}
