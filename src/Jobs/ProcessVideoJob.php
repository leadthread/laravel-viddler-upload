<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Viddler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Zenapply\Viddler\Components\ViddlerClient;

class ProcessVideoJob implements ShouldQueue
{
	use Queueable, SerializesModels;
    
    protected $model;
    protected $client;

	public function __construct(Viddler $model, ViddlerClient $client = null)
	{
		$this->model = $model;
		$this->client = $client;
	}

	public function handle()
    {
    	// For testing
    	if (!empty($this->client)) {
    		$this->model->setClient($this->client);
    	}

    	$this->model->convert()->upload();
    }
}