<?php

namespace Zenapply\Viddler\Jobs;

use File;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;
use Zenapply\Viddler\Exceptions\VideoConversionFailedException;
use Zenapply\Viddler\Models\Video;

class ConvertVideoJob
{
    use Queueable, SerializesModels;

    protected $video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
        $this->map = config('viddler.convert.mimes');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $video = $this->video;

        if($video->isNotFinished() && config('viddler.convert.enabled')) {
            Log::info("Converting video #{$video->id} - NOT IMPLEMENTED YET");

            // Move to appropriate disk
            $video->moveFileToDirectory('converting');
            $video->save();

            // Check if conversion is needed
            if($this->shouldConvert($video)) {

                switch($this->map[$video->mime]) {
                case "video/mp4":
                    $this->convertToMp4($video);
                    break;
                default:
                    throw new VideoConversionFailedException($this->map[$video->mime] . " is not a supported output type.");
                    break;
                }
            }
        }
    }

    protected function convertToMp4(Video $video) {
        $disk = Storage::disk($video->disk);
        $pathDisk = $video->getPathToDisk();
        $pathOld = $video->getPathOnDisk();
        $pathNew = explode(".", $pathOld)[0].".mp4";
        $exit;
        $output = [];
        $command = 'ffmpeg -i '.$pathDisk.$pathOld.' -c copy '.$pathDisk.$pathNew.' 2>&1';
        exec($command,$output,$exit);
        if($exit === 0){
            $parts = explode('/', $pathNew);
            $disk->delete($pathOld);
            $video->mime = File::mimeType($pathDisk.$pathNew);
            $video->extension = File::extension($pathDisk.$pathNew);
            $video->filename = end($parts);
            $video->moveFileToDirectory('converted');
            $video->save();
        } else {
            throw new VideoConversionFailedException(implode(PHP_EOL, $output));
        }

        return $video;
    }

    protected function shouldConvert(Video $video) {
        return array_key_exists($video->mime, $this->map);
    }
}
