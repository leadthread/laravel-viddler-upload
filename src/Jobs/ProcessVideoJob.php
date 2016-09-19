<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Viddler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ProcessVideoJob implements ShouldQueue
{
	use Queueable, SerializesModels;
    
    protected $model;

	public function __construct(Viddler $model)
	{
		$this->model = $model;
	}

	public function handle()
    {
    	$this->model->convert()->upload();
    }
}