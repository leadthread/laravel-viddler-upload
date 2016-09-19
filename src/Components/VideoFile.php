<?php

namespace Zenapply\Viddler\Components;

use Zenapply\Viddler\Models\Viddler;
use Storage;
use File;

class VideoFile
{
	protected $model;

	public function __construct(Viddler $model)
	{
		$this->model = $model;
	}

	public function convert()
	{
		if($this->model->isNotFinished() && config('viddler.convert.enabled')) {

		    // Move to appropriate disk
		    $this->updateStatusTo('converting');

		    // Check if conversion is needed
		    if($this->shouldConvert($this->model)) {

		        switch(config('viddler.convert.instructions')[$this->model->mime]) {
		        case "video/mp4":
		            $this->convertToMp4();
		            break;
		        default:
		            throw new VideoConversionFailedException($this->map[$this->model->mime] . " is not a supported output type.");
		            break;
		        }
		    }
		}
		return $this->model;
	}

	public function updateStatusTo($status)
	{
		if ($this->model->isNotFinished()) {
    		$currentDisk = $this->getDisk();

    		// Do the move
    		$file = $currentDisk->move($this->getPathOnDisk(), "{$status}/{$this->model->filename}");

    		//Update the Model
    		$this->model->path = $status;
    		$this->model->status = $status;
    		$this->model->save();
    	}

    	return $this;
	}

	protected function convertToMp4() {
		$disk = $this->getDisk();
		$pathDisk = $this->getPathToDisk();
		$pathOld = $this->getPathOnDisk();
		$pathNew = explode(".", $pathOld)[0].".mp4";
		$exit;
		$output = [];
		$command = 'ffmpeg -i '.$pathDisk.$pathOld.' -c copy '.$pathDisk.$pathNew.' 2>&1';
		exec($command,$output,$exit);
		if($exit === 0){
			$parts = explode('/', $pathNew);
			$disk->delete($pathOld);
			$this->model->mime = File::mimeType($pathDisk.$pathNew);
			$this->model->extension = File::extension($pathDisk.$pathNew);
			$this->model->filename = end($parts);
			$this->model->updateStatusTo('converted');
		} else {
			throw new VideoConversionFailedException(implode(PHP_EOL, $output));
		}

		return $this;
	}

	protected function shouldConvert(Viddler $model) {
        return array_key_exists($model->mime, config('viddler.convert.instructions'));
    }

	protected function getDisk()
	{
		return Storage::disk($this->model->disk);
	}

	public function getPathOnDisk()
	{
		return "{$this->model->path}/{$this->model->filename}";
	}

	public function getFullPath()
	{
		return $this->getPathToDisk() . $this->getPathOnDisk();
	}

	public function getPathToDisk()
	{
		return $this->getDisk()->getDriver()->getAdapter()->getPathPrefix();
	}
}