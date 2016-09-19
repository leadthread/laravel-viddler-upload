<?php

namespace Zenapply\Viddler\Tests\Mocks;

use Zenapply\Viddler\Components\ViddlerClient;

class ViddlerClientMock extends ViddlerClient
{
	public function prepareUpload()
	{
		return [
            'upload' => [
                'token' => 'foo',
                'endpoint' => 'bar'
            ]
        ];
	}

	public function executeUpload($endpoint, $postFields)
	{
		return [
            'video' => [
                'id' => 'foobar',
            ]
        ];
	}
}