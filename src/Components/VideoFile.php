<?php

namespace Zenapply\Viddler\Upload\Components;

use Zenapply\Viddler\Upload\Exceptions\ViddlerVideoConversionFailedException;
use Zenapply\Viddler\Upload\Models\Viddler;
use Storage;
use File;

class VideoFile
{
    protected $model;

    public function __construct(Viddler &$model)
    {
        $this->model = $model;
    }

    public function convert()
    {
        if ($this->model->isNotFinished() && config('viddler.convert.enabled')) {
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
                        throw new VideoConversionFailedException("{$output} is not a supported output type.");
                }
            }
        }
        return $this->model;
    }

    public function moveTo($status)
    {
        if ($this->model->isNotFinished()) {
            try {
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
            } catch (\League\Flysystem\FileNotFoundException $e) {
                $this->model->status = "error";
                $this->model->path = null;
                $this->model->filename = null;
                $this->model->disk = null;
                $this->model->extension = null;
                $this->model->mime = null;
                $this->model->save();

                throw $e;
            }
        }

        return $this->model;
    }

    protected function convertToMp4()
    {
        $disk = $this->getDisk();
        $pathDisk = $this->getPathToDisk();
        $pathOld = $this->getPathOnDisk();
        $pathNew = explode(".", $pathOld)[0].".mp4";
        $exit = 0;
        $output = [];
        $command = 'ffmpeg -i '.$pathDisk.$pathOld.' -c copy '.$pathDisk.$pathNew.' 2>&1';
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
