<?php

namespace LeadThread\Viddler\Upload;

use Exception;
use Illuminate\Http\UploadedFile;
use Storage;
use LeadThread\Viddler\Upload\Components\ViddlerClient;
use LeadThread\Viddler\Upload\Exceptions\ViddlerIncorrectVideoTypeException;
use LeadThread\Viddler\Upload\Exceptions\ViddlerUploadFailedException;
use LeadThread\Viddler\Upload\Jobs\ProcessVideoJob;
use LeadThread\Viddler\Upload\Models\Viddler;

class Service
{

    /**
     * ViddlerClient
     */
    protected $client = null;

    /**
     * Checks the encoding status of a Viddler video
     */
    public function check(Viddler $model)
    {
        $model = $model->check();
        return $model;
    }

    /**
     * Convert, Upload and Store in Database from a video file
     */
    public function create(UploadedFile $file, $title)
    {
        if ($file->isValid()) {
            //Check file type
            $ok = $this->isMimeSupported($file->getMimeType());

            if ($ok === true) {
                //Store the file
                $filename = $file->hashName();
                $disk = Storage::disk(config('viddler.disk'));
                $disk->putFileAs("new", $file, $filename);

                $class = config('viddler.model');
                
                $video = new $class([
                    'disk' => config('viddler.disk'),
                    'path' => 'new',
                    'filename' => $filename,
                    'title' => $title,
                    'status' => 'new',
                    'mime' => $file->getMimeType(),
                    'extension' => $file->extension(),
                ]);
                $video->save();

                //Done
                return $video;
            } else {
                $msg = [];
                $msg[] = "Incorrect file type!";
                if (is_object($file)) {
                    $msg[] = $file->getClientOriginalExtension();
                    $msg[] = "(".$file->getMimeType().")";
                }
                throw new ViddlerIncorrectVideoTypeException(implode(" ", $msg));
            }
        } else {
            throw new ViddlerUploadFailedException("Upload Failed");
        }
    }

    public function setClient(ViddlerClient $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        if (empty($this->client)) {
            $this->client = new ViddlerClient();
        }
        return $this->client;
    }

    /**
     * Check if a mime is in the supported list
     * @param string|null $mime
     */
    protected function isMimeSupported($mime)
    {
        $supported = $this->getSupportedMimes();
        return in_array($mime, $supported);
    }

    protected function getSupportedMimes()
    {
        return [
            "video/x-msvideo",
            "video/mp4",
            "video/x-m4v",
            "video/x-flv",
            "video/quicktime",
            "video/x-ms-wmv",
            "video/mpeg",
            "video/3gpp",
            "video/x-ms-asf",
            "application/octet-stream"
        ];
    }
}
