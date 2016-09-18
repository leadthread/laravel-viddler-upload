<?php

namespace Zenapply\Viddler\Models;

use Illuminate\Database\Eloquent\Model;
use Storage;
use Zenapply\Viddler\Jobs\CheckVideoJob;
use Zenapply\Viddler\Jobs\ConvertVideoJob;
use Zenapply\Viddler\Jobs\ProcessVideoJob;
use Zenapply\Viddler\Jobs\UploadVideoJob;

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

    /**
     * @param string $dir
     */
    public function moveFileToDirectory($dir)
    {
    	if ($this->isNotFinished()) {
    		$currentDisk = Storage::disk($this->disk);

    		// Do the move
    		$file = $currentDisk->move($this->getPathOnDisk(), "{$dir}/{$this->filename}");

    		//Update the Model
    		$this->path = $dir;
    		$this->status = $dir;
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

	public function finish()
	{
		if ($this->isNotFinished()) {
			$this->moveFileToDirectory('finished');
			$this->status = "finished";
			$this->save();
		}
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

	public function getPathOnDisk() {
		return "{$this->path}/{$this->filename}";
	}

	public function getFullPath() {
		return $this->getPathToDisk() . $this->getPathOnDisk();
	}

	public function getPathToDisk() {
		return Storage::disk($this->disk)->getDriver()->getAdapter()->getPathPrefix();
	}
}