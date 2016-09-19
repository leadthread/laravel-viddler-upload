<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Viddler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ProcessVideoJob implements ShouldQueue
{
	use Queueable, SerializesModels;
    
    protected $video;

	public function __construct(Viddler $video)
	{
		$this->video = $video;
	}

	public function handle()
    {
    	$this->video
    		->convert()
    		->upload();
    }
}