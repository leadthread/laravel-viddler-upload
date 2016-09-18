<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Log;

class CheckVideoJob implements ShouldQueue
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
            Log::info("Checking video #{$video->id} - NOT IMPLEMENTED YET");
    		$video->moveToDisk('encoding');

    		// Finish up!
    		$video->moveToDisk('finished');
    	}
    }
}