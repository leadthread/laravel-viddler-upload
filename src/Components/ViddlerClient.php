<?php

namespace Zenapply\Viddler\Components;

use Zenapply\Viddler\Models\Viddler;
use Zenapply\Viddler\Exceptions\ViddlerException;
use Zenapply\Viddler\Exceptions\ViddlerNotFoundException;
use Viddler_V2;

class ViddlerClient
{
	protected $client;
	protected $session_id;
	protected $record_token;

	protected function prepareUpload()
	{
		if(empty($this->session_id)) {
			$this->auth();
		}

		return $this->checkResponseForErrors($this->client->viddler_videos_prepareUpload([
			'response_type' => 'json', 
			'sessionid' => $this->session_id
		]));
	}

	protected function executeUpload($endpoint, $postFields)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		$response    = curl_exec($ch);
		$info        = curl_getinfo($ch);
		$header_size = $info['header_size'];
		// $header      = substr($response, 0, $header_size);
		$result      = unserialize(substr($response, $header_size));
		curl_close($ch);

		return $result;
	}

	public function upload(Viddler $model)
	{
		//Fire Event
		$model->updateStatusTo('uploading');
		
		//Path
		$file = $model->getFile();
		$path = $file->getFullPath();

		$response = $this->prepareUpload();

		$token      = $response['upload']['token'];
		$endpoint   = $response['upload']['endpoint'];

		//Prepare the data!
		$postFields = array();
		$postFields['callback'] = $model->callback;
		$postFields['description'] = "";
		$postFields['tags'] = "";
		$postFields['title'] = $model->title;
		$postFields['uploadtoken'] = $token;
		$postFields['view_perm'] = "embed";
		$postFields['file'] = curl_file_create($path, $model->mime);
		
		//Send it!
		$result = $this->executeUpload($endpoint, $postFields);

		if(empty($result['video']['id'])){
            throw new ViddlerException('Viddler did not return a video id!');
        }

        $model->viddler_id = $result['video']['id'];
        $model->uploaded = true;
        $model->updateStatusTo('encoding');

		return $model;
	}

	/**
	 * Authenticate with viddler
	 */
	protected function auth()
	{
		$key  = config('viddler.auth.key');
        $user = config('viddler.auth.user');
        $pass = config('viddler.auth.pass');

        //Create Client
        if (empty($this->client)) {
        	$this->client = new Viddler_V2($key);
        }

        $resp = $this->client->viddler_users_auth(array('user' => $user, 'password' => $pass));
        $resp = $this->checkResponseForErrors($resp);

        $this->session_id = $resp['auth']['sessionid'];
        if(!empty($resp['auth']['record_token'])) {
        	$this->record_token = $resp['auth']['record_token'];
        }
	}

	/**
	 * Return the session id
	 */
	protected function getSessionId() {
		if(empty($this->session_id)) {
			$this->auth();
		}
		return $this->session_id;
	}

	/**
	 * Return the record token for this session
	 */
	protected function getRecordToken() {
		if(empty($this->session_id)) {
			$this->auth();
		}
		return $this->record_token;
	}

	protected function checkResponseForErrors($response) {
		if(isset($response["error"])){
			$msg = [];

			$msg[] = "Viddler Error";
			if (!empty($response["error"]["code"])) {
				$msg[] = "Code: ".$response["error"]["code"];
			}
			if (!empty($response["error"]["description"])) {
				$msg[] = "Description: ".$response["error"]["description"];
			}
			if (!empty($response["error"]["details"])) {
				$msg[] = "Details: ".$response["error"]["details"];
			}

			$msg = implode(" | ", $msg);

			switch($response["error"]["code"]){
			case "100":
				throw new ViddlerNotFoundException($msg);
			default:
				throw new ViddlerException($msg);
			}
		}

		return $response;
	}
}