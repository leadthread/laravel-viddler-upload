<?php

namespace Zenapply\Viddler\Jobs;

use Zenapply\Viddler\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Log;

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
            $video->moveToDisk('converting');

            // Check if conversion is needed
            if($this->shouldConvert()) {
                // //Fire Event
                // $video->status = 'converting';
                // $video->save();

                // $exit;
                // $output = [];
                // $command = 'ffmpeg -i '.$video->directory.$video->filename.$video->extension.' -c copy '.$video->directory.$video->filename.'.mp4 2>&1';
                // exec($command,$output,$exit);
                // if($exit === 0){
                //     //Delete the old video
                //     Storage::disk('tmp')->delete($video->filename.$video->extension);
                //     $video->mime = 'video/mp4';
                //     $video->extension = '.mp4';
                //     $video->status = 'converted';
                //     $video->save();
                // }
            }
        }
    }

    protected function shouldConvert() {
        return array_key_exists($this->video->mime, config('viddler.convert.mimes'));
    }
}
