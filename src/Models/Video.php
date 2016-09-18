<?php

namespace Zenapply\Viddler\Models;

use Illuminate\Database\Eloquent\Model;
use Zenapply\Viddler\Jobs\CheckVideoJob;
use Zenapply\Viddler\Jobs\ConvertVideoJob;
use Zenapply\Viddler\Jobs\ProcessVideoJob;
use Zenapply\Viddler\Jobs\UploadVideoJob;
use Storage;

class Video extends Model
{
	protected $guarded = ['id'];
	protected $table;

	public function __construct(array $attributes = [])
    {
        if (empty($this->table)) {
            $this->table = config('viddler.table');
        }
        parent::__construct($attributes);
    }

    public function moveToDisk($step)
    {
    	if ($this->isNotFinished()) {
    		echo $this->disk.PHP_EOL;
    		$currentDisk = Storage::disk($this->disk);
    		$destinationDiskName = config("viddler.storage.disks.{$step}");
    		$destinationDisk = Storage::disk($destinationDiskName);

    		// Do the move
    		$file = $currentDisk->get($this->filename);
    		$destinationDisk->put($this->filename, $file);
    		$currentDisk->delete($this->filename);

    		//Update the Model
    		$this->disk = $destinationDiskName;
    		$this->save();
    	}

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

	public function start()
	{
		dispatch(new ProcessVideoJob($this));
		return $this;
	}

	public function convert()
	{
		dispatch(new ConvertVideoJob($this));
		return $this;
	}

	public function upload()
	{
		dispatch(new UploadVideoJob($this));
		return $this;
	}

	public function check()
	{
		dispatch(new CheckVideoJob($this));
		return $this;
	}
}