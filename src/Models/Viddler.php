<?php

namespace Zenapply\Viddler\Models;

use Illuminate\Database\Eloquent\Model;
use Zenapply\Viddler\Components\VideoFile;
use Zenapply\Viddler\Components\ViddlerClient;

class Viddler extends Model
{
	protected $guarded = ['id'];
	protected $table;
	public $file;
	public $client;

	public function __construct(array $attributes = [])
    {
        if (empty($this->table)) {
            $this->table = config('viddler.table');
        }
        parent::__construct($attributes);

        $this->file = new VideoFile($this);
        $this->client = new ViddlerClient();
    }

    public function convert()
    {
        $this->file->convert();
        return $this;
    }

    public function upload()
    {
        $this->client->upload($this->file);
        return $this;
    }

    public function updateStatusTo($status)
    {
    	$this->file->updateStatusTo($status);
        return $this;
    }

    public function isFinished()
    {
    	return $this->status === "finished";
    }

    public function isNotFinished()
    {
    	return !$this->isFinished();
    }
}