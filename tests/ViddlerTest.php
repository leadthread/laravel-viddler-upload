<?php

namespace Zenapply\Viddler\Tests;

use Zenapply\Viddler\Models\Viddler;
use Zenapply\Viddler\Service;
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
        $service = new Service();
        $this->assertInstanceOf(Service::class,$service);
    }

    public function testConvertingMovToMp4()
    {
        $service = new Service();
        $file = new UploadedFile(__DIR__."/files/small.mov", "small.mov");
        $model = $service->create($file, "Test");
        $model = Viddler::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
    }

    public function testItFailsWhenUploadingANonVideoFile()
    {
        $this->setExpectedException(IncorrectVideoTypeException::class);
        $title = "This is a test video";
        $service = new Service();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt");
        $model = $service->create($file, $title);
    }

    public function testUploadingAVideo()
    {
        $title = "This is a test video";
        $service = new Service();
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
        $model = $service->create($file, $title);
        $model = Viddler::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals($title, $model->title);
    }
}
