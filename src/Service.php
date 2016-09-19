<?php

namespace Zenapply\Viddler;

use Exception;
use Illuminate\Http\UploadedFile;
use Storage;
use Zenapply\Viddler\Exceptions\IncorrectVideoTypeException;
use Zenapply\Viddler\Models\Viddler;
use Zenapply\Viddler\Jobs\ProcessVideoJob;

class Service {
	protected $client;

	/**
	 * Convert, Upload and Store in Database from a video file
	 */
    public function create(UploadedFile $file, $title)
    {
        //Check file type
        $ok = $this->isMimeSupported($file->getMimeType());

        if($ok === true){
            //Store the file
            $filename = $file->hashName();
            $disk = Storage::disk(config('viddler.disk'));
			$contents = file_get_contents($file->getRealPath());
			$disk->put("new/".$filename, $contents);
            $video = Viddler::create([
				'disk' => config('viddler.disk'),
				'path' => 'new',
				'filename' => $filename,
				'title' => $title,
				'status' => 'new',
				'mime' => $file->getMimeType(),
				'extension' => $file->extension(),
			]);

            //Convert File
            dispatch(new ProcessVideoJob($video));

            //Done
            return $video;
        } else {
        	$msg = [];
        	$msg[] = "Incorrect file type!";
            if(is_object($file)){
                $msg[] = $file->getClientOriginalExtension();
                $msg[] = "(".$file->getMimeType().")";
            }
            throw new IncorrectVideoTypeException(implode(" ", $msg));
        }
    }

    /**
     * Get the status of a viddler video
     */
    public function status($id) 
    {
    	throw new Exception("Not Implemented");	
    }

	/**
	 * Saves the uploaded file to the waiting disk
	 * @param string $filename
	 */
	protected function saveVideoToNewDisk(UploadedFile $file, $filename) {
		$disk = Storage::disk(config('viddler.disk'));
		$contents = file_get_contents($file->getRealPath());
		$disk->put("new/".$filename, $contents);
	}

	/**
	 * Saves the uploaded file to the waiting disk
	 * @param string $filename
	 */
	protected function saveVideoToDatabase(UploadedFile $file, $filename, $title) {
		
	}

	/**
	 * Check if a mime is in the supported list
	 * @param string|null $mime
	 */
	protected function isMimeSupported($mime) {
		$supported = $this->getSupportedMimes();
		return in_array($mime, $supported);
	}

	protected function checkResponseForErrors($response) {
		if(isset($response["error"])){
			$msg = [];
			$msg[] = "Viddler Error Code: ".$response["error"]["code"];
			$msg[] = $response["error"]["description"];
			$msg[] = $response["error"]["details"];

			$msg = implode(" | ", $msg);

			switch($response["error"]["code"]){
			case "100":
				throw new ViddlerVideoNotFoundException($msg);
				break;
			default:
				throw new ViddlerException($msg);
				break;
			}
		}

		return $response;
	}

	protected function getSupportedMimes() {
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
