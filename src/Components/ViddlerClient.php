<?php

namespace Zenapply\Viddler\Components;

use Zenapply\Viddler\Models\Viddler;
use ViddlerV2;

class ViddlerClient
{
	protected $session_id;
	protected $record_token;

	public function upload(VideoFile $file)
	{
		$file->updateStatusTo("encoding");
	}

	public function check(Viddler $model)
	{

	}

	/**
	 * Authenticate with viddler
	 */
	protected function auth()
	{
		$key  = Config::get('viddler.auth.key');
        $user = Config::get('viddler.auth.user');
        $pass = Config::get('viddler.auth.pass');

        //Create Client
        if (empty($this->client)) {
        	$this->client = new ViddlerV2($key);
        }

        $resp = $this->client->viddler_users_auth(array('user' => $user, 'password' => $pass));
        $resp = $this->checkResponseForErrors($resp);
        $this->$session_id = $this->auth['auth']['sessionid'];
        $this->$record_token = $this->auth['auth']['record_token'];
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
}