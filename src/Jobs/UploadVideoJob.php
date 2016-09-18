<?php

namespace Zenapply\Viddler\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Zenapply\Viddler\Models\Video;

class UploadVideoJob implements ShouldQueue
{
	use Queueable, SerializesModels;
    
    protected $video;

	public function __construct(Video $video)
	{
		$this->video = $video;
	}

	public function handle()
    {
    	$video = $this->video;
    	if ($video->isNotFinished()) {
            Log::info("Uploading video #{$video->id} - NOT IMPLEMENTED YET");
    		$video->moveFileToDirectory('uploading');

            //Fire Event
            $this->video->status = 'uploading';
            $this->video->save();

            $response = $this->viddler->viddler_videos_prepareUpload(array('response_type' => 'json', 'sessionid' => $this->sess_id));
            
            $token=$response['upload']['token'];
            $endpoint=$response['upload']['endpoint'];
            $callback = '';
            $postFields = array();

            //Path
            $path = $this->video->directory.$this->video->filename.$this->video->extension;

            //metaData
            $postFields['title'] = "Zenapply Video ".$this->video->filename;
            $postFields['view_perm'] = "embed";
            $postFields['description'] = "";
            $postFields['tags'] = "";
            $postFields['callback'] = $callback;
            $postFields['uploadtoken'] = $token;
            
            //files
            $postFields['file'] = curl_file_create($path, $this->video->mime, $this->video->filename.$this->video->extension);

            Log::debug("Video #{$this->video->id} @ {$path} | " . (file_exists($path) ? "exists!" : "does not exist!"));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_NOBODY, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            $response     = curl_exec($ch);
            $info         = curl_getinfo($ch);
            $header_size  = $info['header_size'];
            $header       = substr($response, 0, $header_size);
            $result       = unserialize(substr($response, $header_size));
            curl_close($ch);

            //Fire Event
            Storage::disk('tmp')->delete($this->video->filename.$this->video->extension);
            $this->video->viddler_id = $result['video']['id'];
            $this->video->uploaded = true;
            $this->video->directory = null;
            $this->video->filename = null;
            $this->video->extension = null;
            $this->video->mime = null;
            $this->video->status = 'encoding';
            $this->video->save();

            if(empty($this->video->viddler_id)){
                Log::critical('Viddler did not return a video id!',[$response]);
                throw new ViddlerException('Viddler did not return a video id!');
            }

            return $this->video;
    	}
    }
}