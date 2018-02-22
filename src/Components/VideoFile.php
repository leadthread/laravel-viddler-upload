<?php

namespace LeadThread\Viddler\Upload\Components;

use LeadThread\Viddler\Upload\Traits\CanLog;
use LeadThread\Viddler\Upload\Exceptions\ViddlerVideoConversionFailedException;
use LeadThread\Viddler\Upload\Models\Viddler;
use Storage;
use File;

class VideoFile
{
    use CanLog;

    protected $model;

    public function __construct(Viddler &$model)
    {
        $this->model = $model;
    }

    public function convert()
    {
        $this->info("{$this->model}'s video file is starting convert");
        if ($this->model->isNotResolved() && config('viddler.convert.enabled')) {
            // Move to appropriate disk
            $this->model->updateStatusTo('converting');

            // Check if conversion is needed
            if ($this->shouldConvert($this->model)) {
                $output = config('viddler.convert.instructions')[$this->model->mime];
                switch ($output) {
                    case "video/mp4":
                        $this->convertToMp4();
                        break;
                    default:
                        throw new ViddlerVideoConversionFailedException("{$output} is not a supported output type.");
                }
            }
        }
        return $this->model;
    }

    public function moveTo($status)
    {
        $this->info("{$this->model}'s video file is moving to {$status}");
        if ($this->model->isNotResolved()) {
            $disk = $this->getDisk();
            $dest = "{$status}/{$this->model->filename}";

            // Delete a prexisting file
            if ($disk->exists($dest)) {
                $disk->delete($dest);
            }

            // Do the move
            $disk->move($this->getPathOnDisk(), $dest);

            //Update the Model
            $this->model->path = $status;
            $this->model->save();
        }

        return $this->model;
    }

    protected function convertToMp4()
    {
        $this->info("{$this->model}'s video file is coverting to mp4");
        $disk = $this->getDisk();
        $pathDisk = $this->getPathToDisk();
        $pathOld = $this->getPathOnDisk();
        $pathNew = explode(".", $pathOld)[0].".mp4";
        $exit = 0;
        $output = [];
        $command = 'ffmpeg -i '.$pathDisk.$pathOld.' -strict -2 -c:a aac -c:v copy '.$pathDisk.$pathNew.' 2>&1';
        exec($command, $output, $exit);
        if ($exit === 0) {
            $parts = explode('/', $pathNew);
            $disk->delete($pathOld);
            $this->model->mime = File::mimeType($pathDisk.$pathNew);
            $this->model->extension = File::extension($pathDisk.$pathNew);
            $this->model->filename = end($parts);
            $this->model->updateStatusTo('converted');
        } else {
            throw new ViddlerVideoConversionFailedException(implode(PHP_EOL, $output));
        }

        return $this;
    }

    public function removeFile()
    {
        if ($this->model->status !== 'error' && !empty($this->model->disk)) {
            $this->info("{$this->model}'s video file is being removed");
            $disk = $this->getDisk();
            $dest = "{$this->model->path}/{$this->model->filename}";

            // Delete a prexisting file
            if ($disk->exists($dest)) {
                $disk->delete($dest);
            }
        }
    }

    protected function shouldConvert(Viddler $model)
    {
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
