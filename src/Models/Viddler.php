<?php

namespace Zenapply\Viddler\Upload\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Zenapply\Viddler\Upload\Components\ViddlerClient;
use Zenapply\Viddler\Upload\Components\VideoFile;

/**
  * @property boolean $uploaded
  * @property string  $callback
  * @property string  $disk
  * @property string  $extension
  * @property string  $filename
  * @property string  $mime
  * @property string  $path
  * @property string  $status
  * @property string  $title
  * @property string  $viddler_id
  */
class Viddler extends Model
{
    protected $guarded = ['id'];
    protected $table;
    protected $client;
    public $file;

    public function __construct(array $attributes = [])
    {
        if (empty($this->table)) {
            $this->table = config('viddler.table');
        }
        parent::__construct($attributes);

        $this->file = new VideoFile($this);

        $this->client = $this->getClient();
    }

    public function convert()
    {
        try {
            $this->file->convert();
        } catch (Exception $e) {
            $this->updateStatusTo("error");
            throw $e;
        }
        return $this;
    }

    public function upload()
    {
        try {
            $this->client->upload($this);
        } catch (Exception $e) {
            $this->updateStatusTo("error");
            throw $e;
        }
        return $this;
    }

    public function getFile()
    {
        return $this->file;
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

    public function setClient(ViddlerClient $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        if (empty($this->client)) {
            $this->client = new ViddlerClient();
        }
        return $this->client;
    }
}
