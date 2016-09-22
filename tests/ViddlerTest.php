<?php

namespace Zenapply\Viddler\Upload\Tests;

use Illuminate\Http\UploadedFile;
use Storage;
use Exception;
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

    public function testCheckEncoding()
    {
        $model = $this->uploadFile("small.mp4", "This is a test video");
        $model = ViddlerFacade::check($model);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/finished/'.$model->filename));
        $this->assertEquals(100, $model->encoding_progress);
        $this->assertEquals("finished", $model->status);
    }

    public function testFileErrorsCorrectly()
    {
        $this->setExpectedException(Exception::class);

        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Use the service
        $client = new ViddlerClientMockThrowsExceptions();
        $model = $this->uploadFile("small.mp4", "This is a test video", $client);

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
        $model = $this->uploadFile("small.mp4", "This is a test video");
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
        $model = $this->uploadFile("small.mp4", "This is a test video");

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals("This is a test video", $model->title);
    }

    public function testConvertingMovToMp4()
    {
        $model = $this->uploadFile("small.mov", "This is a test video");

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/encoding/'.$model->filename));
        $this->assertEquals("video/mp4", $model->mime);
        $this->assertEquals("This is a test video", $model->title);
    }

    public function testItFailsWhenUploadingANonVideoFile()
    {
        $this->setExpectedException(ViddlerIncorrectVideoTypeException::class);
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt");
        $model = $service->create($file, "This is a test video");
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

    protected function uploadFile($filename, $title, $client = null)
    {
        if (empty($client)) {
            $client = new ViddlerClientMock();
        }
        $file = new UploadedFile(__DIR__."/files/".$filename, $filename);
        $model = ViddlerFacade::create($file, $title);
        $model->setClient($client);
        $model->convert();
        $model->upload();
        return $model;
    }
}
