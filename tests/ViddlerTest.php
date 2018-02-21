<?php

namespace LeadThread\Viddler\Upload\Tests;

use Illuminate\Http\UploadedFile;
use Storage;
use Exception;
use Viddler as ViddlerFacade;
use LeadThread\Viddler\Upload\Exceptions\ViddlerIncorrectVideoTypeException;
use LeadThread\Viddler\Upload\Exceptions\ViddlerUploadFailedException;
use LeadThread\Viddler\Upload\Models\Viddler;
use LeadThread\Viddler\Upload\Service;
use LeadThread\Viddler\Upload\Tests\Mocks\ViddlerClientMock;
use LeadThread\Viddler\Upload\Tests\Mocks\ViddlerClientMockThrowsExceptions;

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
        $this->expectException(Exception::class);

        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Use the service
        $client = new ViddlerClientMockThrowsExceptions();
        $model = $this->uploadFile("small.mp4", "This is a test video", $client);

        $this->assertEquals(true, file_exists(__DIR__.'/tmp/error/'.$model->filename));
        $this->assertEquals("error", $model->status);
    }

    public function testFileErrorsCorrectlyWhenFileIsMissing()
    {
        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Use the service
        $model = $this->uploadFile("small.mp4", "This is a test video");

        Storage::disk($model->disk)->delete($model->path ."/". $model->filename);

        try {
            ViddlerFacade::check($model);
            $this->fail("Exception was not thrown!");
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $model = Viddler::findOrFail($model->id);
            $this->assertEquals("error", $model->status);
            $this->assertEquals(null, $model->path);
            $this->assertEquals(null, $model->filename);
            $this->assertEquals(null, $model->disk);
            $this->assertEquals(null, $model->extension);
        }
    }

    /**
     * This test is supposed to check to see if there is an existing
     * video file and just use that one instead of saving the new one
     */
    public function testUploadingAVideoThatHasAPreExistingFileOnTheServer()
    {
        //Create the file
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");

        //Store a duplicate before hand
        $filename = $file->hashName();
        $disk = Storage::disk(config('viddler.disk'));
        $contents = file_get_contents($file->getRealPath());
        $disk->put("encoding/".$filename, $contents);

        //Use the service. This should just use the file that we just put in the encoding directory
        $model = $this->uploadFile("small.mp4", "This is a test video");
        $this->assertTrue(true); // This whole test should not have thrown an exception.
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
        $this->expectException(ViddlerIncorrectVideoTypeException::class);
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/sample.txt", "sample.txt", null, null, null, true);
        $model = $service->create($file, "This is a test video");
    }

    public function testItFailsWhenUploadingAnInvalidFile()
    {
        $this->expectException(ViddlerUploadFailedException::class);
        $service = $this->getServiceWithMockedClient();
        $file = new UploadedFile(__DIR__."/files/small.mp4", "small.mp4");
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

        $file = new UploadedFile(__DIR__."/files/".$filename, $filename, null, null, null, true);
        $model = ViddlerFacade::create($file, $title);
        $model->setClient($client);
        $model->convert();
        $model->upload();

        return $model;
    }
}
