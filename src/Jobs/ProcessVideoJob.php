<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ProcessVideoJob implements ShouldQueue
{
	use Queueable, SerializesModels;
    
    protected $video;

	public function __construct(Video $video)
	{
		$this->video = $video;
	}

	public function handle()
    {
    	$this->video->convert();
    	$this->video->upload();
    	$this->video->check();
    }
}