<?php

namespace Zenapply\Viddler\Upload\Tests\Mocks;

use Zenapply\Viddler\Upload\Components\ViddlerClient;

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