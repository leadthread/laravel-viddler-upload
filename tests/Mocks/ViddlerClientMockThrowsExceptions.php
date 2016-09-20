<?php

namespace Zenapply\Viddler\Upload\Tests\Mocks;

use Zenapply\Viddler\Upload\Components\ViddlerClient;

class ViddlerClientMockThrowsExceptions extends ViddlerClient
{
	public function prepareUpload()
	{
		throw new \Exception('test');
	}

	public function executeUpload($endpoint, $postFields)
	{
        throw new \Exception('test');
	}
}