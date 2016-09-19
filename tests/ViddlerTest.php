<?php

namespace Zenapply\Viddler\Upload\Tests;

use Zenapply\Viddler\Upload\Models\Viddler;
use Zenapply\Viddler\Upload\Service;
use Viddler as ViddlerFacade;
use Illuminate\Http\UploadedFile;
use Zenapply\Viddler\Upload\Tests\Mocks\ViddlerClientMock;
use Zenapply\Viddler\Upload\Exceptions\IncorrectVideoTypeException;

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

    public function testViddlerFacade(){
        $obj = ViddlerFacade::getFacadeRoot();
        $this->assertInstanceOf(Service::class,$obj);
    }

    public function testUploadingAVideo()
    {
        $title = "This is a test video";
        $callback = "https://mydomain.com/video/callback";
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
        $model = $service->create($file, $title, $callback);
        $model = Viddler::find($model->id);
        
        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals($title, $model->title);
        $this->assertEquals($callback, $model->callback);
    }

    public function testConvertingMovToMp4()
    {
        $title = "This is a test video";
        $callback = "https://mydomain.com/video/callback";
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/small.mov", "small.mov");
        $model = $service->create($file, $title, $callback);
        $model = Viddler::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals($title, $model->title);
        $this->assertEquals($callback, $model->callback);
    }

    public function testItFailsWhenUploadingANonVideoFile()
    {
        $this->setExpectedException(IncorrectVideoTypeException::class);
        $title = "This is a test video";
        $callback = "https://mydomain.com/video/callback";
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt");
        $model = $service->create($file, $title, $callback);
    }

    protected function getServiceWithMockedClient()
    {
        $client = new ViddlerClientMock();

        $service = new Service();
        $service->setClient($client);

        return $service;
    }
}
