<?php

namespace Zenapply\Viddler\Upload;

use Exception;
use Illuminate\Http\UploadedFile;
use Storage;
use Zenapply\Viddler\Upload\Components\ViddlerClient;
use Zenapply\Viddler\Upload\Exceptions\ViddlerIncorrectVideoTypeException;
use Zenapply\Viddler\Upload\Jobs\ProcessVideoJob;
use Zenapply\Viddler\Upload\Models\Viddler;

class Service
{

    /**
     * ViddlerClient
     */
    protected $client = null;

    /**
     * Convert, Upload and Store in Database from a video file
     */
    public function create(UploadedFile $file, $title, $callback)
    {
        //Check file type
        $ok = $this->isMimeSupported($file->getMimeType());

        if ($ok === true) {
            //Store the file
            $filename = $file->hashName();
            $disk = Storage::disk(config('viddler.disk'));
            $contents = file_get_contents($file->getRealPath());
            $disk->put("new/".$filename, $contents);
            
            $video = new Viddler([
                'disk' => config('viddler.disk'),
                'path' => 'new',
                'filename' => $filename,
                'title' => $title,
                'callback' => $callback,
                'status' => 'new',
                'mime' => $file->getMimeType(),
                'extension' => $file->extension(),
            ]);
            $video->save();

            //Convert File
            dispatch(new ProcessVideoJob($video, $this->client));

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
