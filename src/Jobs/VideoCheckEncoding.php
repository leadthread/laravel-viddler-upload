<?php

namespace App\Jobs;

use App\Events\VideoError;
use App\Library\Viddler\ViddlerV2;
use App\Video;
use Carbon\Carbon;
use Config;
use Exception;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;
use Notify;

class VideoCheckEncoding extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;

    protected $video;
    protected $key;
    protected $user;
    protected $pass;
    protected $viddler;
    protected $sess_id;

    /**
     * This property will toggle sending notifications when batch jobs finish
     * @var boolean
     */
    protected $notify = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
        Log::info('Checking Encoding Status of Video: #'.$this->video->id);
        
        if($this->video->status === 'encoding'){

            if(empty($this->video->viddler_id)){
                Log::critical('Something is wrong... videos should always have viddler_id\'s when they get to this step...');
                Notify::critical('Something is wrong... videos should always have viddler_id\'s when they get to this step...');
                $this->video->delete();
                return;
            }

            try{
                $this->authenticate();
                $this->check();
            } catch(Exception $e) {
                event(new VideoError($this->video,$e->getMessage()));
                $this->video->status = "error";
                $this->video->save();
            }
        } else {
            Log::warning('Video is not at the encoding step!');
        }
    }

    private function authenticate(){
        $this->key = Config::get('viddler.key');
        $this->user = Config::get('viddler.user');
        $this->pass = Config::get('viddler.pass');

        //create viddler
        $this->viddler = new ViddlerV2($this->key);
        $this->auth = $this->viddler->viddler_users_auth(array('user' => $this->user, 'password' => $this->pass));
        $this->sess_id = (!empty($this->auth['auth']['sessionid']) ? $this->auth['auth']['sessionid'] : null);
    }

    private function check(){
        $response = $this->viddler->viddler_encoding_getStatus(array('video_id' => $this->video->viddler_id, 'response_type' => 'json', 'sessionid' => $this->sess_id)); 
        
        $files = $response["list_result"]["files"];

        if(count($files)<1){
            throw new Exception("No files were returned from viddler");
        } else {
            $this->logProgress($files);
            if($this->isStatus($files,"success"))
            {
                $this->video->status = "complete";
                $this->video->save();
            } 
            else if($this->isStatus($files,"error"))
            {
                Log::critical("Viddler returned 'error' for video {$this->video->viddler_id}");
                Notify::critical("Viddler returned 'error' for video {$this->video->viddler_id}");
                $this->video->delete();
            } 
            else 
            {
                $secondsSinceCreation = $this->video->created_at->diffInSeconds(Carbon::now());
                if($secondsSinceCreation<1800){
                    $job = (new VideoCheckEncoding($this->video))->delay(5)->onQueue(gethostname().'-zenapply-'.env('APP_ENV','default').'-videos');
                    $this->dispatch($job);
                } else {
                    throw new Exception('Video Encoding Timed Out!');
                }
            }
        }
    }

    private function avgEncodingProgress($files){
        $total=0;
        $count=0;
        foreach ($files as $file) {
            $total += intval($file["encoding_progress"]);
            $count += 1;
        }
        if($count=0){
            return 0;
        }
        return $total/$count;
    }

    /**
     * @param string $isStatus
     */
    private function isStatus($files,$isStatus){
        $ans=null;
        foreach ($files as $file) {
            if($file["profile_name"] === "360p"){
                $status = $file["encoding_status"];
                $ans = $status===$isStatus;
            }
        }

        if($ans === null){
            throw new Exception("When encoding a Viddler video I could not find a 360p version!");
        }

        return $ans;
    }

    private function hasStatus($files,$isStatus){
        $ans=false;
        foreach ($files as $file) {
            $status = $file["status"];
            if($status===$isStatus)
                return true;
        }
        return $ans;
    }

    private function logProgress($files){
        $c = collect($files);
        $progress = $c->sum('encoding_progress')/$c->count();
        $statuses = implode("|",$c->pluck('encoding_status')->unique()->all());
        Log::info("Encoding Status of Video #{$this->video->id}: {$progress}% {$statuses}");
    }
}
