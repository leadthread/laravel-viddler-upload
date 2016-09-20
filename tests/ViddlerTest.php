<?php

namespace Zenapply\Viddler\Upload\Tests;

use Illuminate\Http\UploadedFile;
use Storage;
use Viddler as ViddlerFacade;
use Zenapply\Viddler\Upload\Exceptions\ViddlerIncorrectVideoTypeException;
use Zenapply\Viddler\Upload\Models\Viddler;
use Zenapply\Viddler\Upload\Service;
use Zenapply\Viddler\Upload\Tests\Mocks\ViddlerClientMock;
use Zenapply\Viddler\Upload\Tests\Mocks\ViddlerClientMockThrowsExceptions;

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

    public function testFileErrorsCorrectly()
    {
        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Use the service
        $client = new ViddlerClientMockThrowsExceptions();
        $service = $this->getServiceWithMockedClient($client);
        $model = $service->create($file, "This is a test video", "https://mydomain.com/video/callback");
        $model = Viddler::find($model->id);
        
        $this->assertEquals(true, file_exists(__DIR__.'/tmp/error/'.$model->filename));
        $this->assertEquals("error", $model->status);
    }

    public function testUploadingAVideoThatHasAPreExistingFileOnTheServer()
    {
        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Store a duplicate
        $filename = $file->hashName();
        $disk = Storage::disk(config('viddler.disk'));
        $contents = file_get_contents($file->getRealPath());
        $disk->put("encoding/".$filename, $contents);

        //Use the service
        $service = $this->getServiceWithMockedClient();
        $model = $service->create($file, "This is a test video", "https://mydomain.com/video/callback");
    }

    public function testItCreatesAnInstanceOfViddler()
    {
        $service = new Service();
        $this->assertInstanceOf(Service::class, $service);
    }

    public function testViddlerFacade()
    {
        $obj = ViddlerFacade::getFacadeRoot();
        $this->assertInstanceOf(Service::class, $obj);
    }

    public function testUploadingAVideo()
    {
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
        $model = $service->create($file, "This is a test video", "https://mydomain.com/video/callback");

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals("This is a test video", $model->title);
        $this->assertEquals("https://mydomain.com/video/callback", $model->callback);
    }

    public function testConvertingMovToMp4()
    {
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/small.mov", "small.mov");
        $model = $service->create($file, "This is a test video", "https://mydomain.com/video/callback");

        // Because converting is async
        $model = Viddler::find($model->id);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals("This is a test video", $model->title);
        $this->assertEquals("https://mydomain.com/video/callback", $model->callback);
    }

    public function testItFailsWhenUploadingANonVideoFile()
    {
        $this->setExpectedException(ViddlerIncorrectVideoTypeException::class);
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt");
        $model = $service->create($file, "This is a test video", "https://mydomain.com/video/callback");
    }

    protected function getServiceWithMockedClient($client = null)
    {
        if (empty($client)) {
            $client = new ViddlerClientMock();
        }

        $service = new Service();
        $service->setClient($client);

        return $service;
    }
}
