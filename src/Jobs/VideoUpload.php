<?php

namespace App\Jobs;

use App\Video;
use App\Company;
use App\Library\Viddler\ViddlerV2;
use App\Jobs\VideoCheckEncoding;
use App\Events\VideoError;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Config;
use Storage;
use Log;
use Exception;
use Notify;

class VideoUpload extends Job implements SelfHandling, ShouldQueue
{
    use DispatchesJobs;

    protected $company;
    protected $video;
    protected $key;
    protected $user;
    protected $pass;
    protected $viddler;
    protected $sess_id;

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
        try{
            Log::info('Uploading Video: #'.$this->video->id);
            $this->company = Company::where('id',$this->video->cid)->first();
            $this->authenticate();
            $this->upload();
            $this->dispatch((new VideoCheckEncoding($this->video))->delay(5)->onQueue(gethostname().'-zenapply-'.env('APP_ENV','default').'-videos'));
        } catch(Exception $e) {
            event(new VideoError($this->video,$e->getMessage()));
            $this->video->status = "error";
            $this->video->save();
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

    private function upload(){
        //Fire Event
        $this->video->status = 'uploading';
        $this->video->save();

        $response = $this->viddler->viddler_videos_prepareUpload(array('response_type' => 'json', 'sessionid' => $this->sess_id));
        
        $token=$response['upload']['token'];
        $endpoint=$response['upload']['endpoint'];
        $callback = '';
        $postFields = array();

        //Path
        $path = $this->video->directory.$this->video->filename.$this->video->extension;

        //metaData
        $postFields['title'] = "Zenapply Video ".$this->video->filename;
        $postFields['view_perm'] = "embed";
        $postFields['description'] = "";
        $postFields['tags'] = "";
        $postFields['callback'] = $callback;
        $postFields['uploadtoken'] = $token;
        
        //files
        $postFields['file'] = curl_file_create($path, $this->video->mime, $this->video->filename.$this->video->extension);

        Log::debug("Video #{$this->video->id} @ {$path} | " . (file_exists($path) ? "exists!" : "does not exist!"));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $response     = curl_exec($ch);
        $info         = curl_getinfo($ch);
        $header_size  = $info['header_size'];
        $header       = substr($response, 0, $header_size);
        $result       = unserialize(substr($response, $header_size));
        curl_close($ch);

        //Fire Event
        Storage::disk('tmp')->delete($this->video->filename.$this->video->extension);
        $this->video->viddler_id = $result['video']['id'];
        $this->video->uploaded = true;
        $this->video->directory = null;
        $this->video->filename = null;
        $this->video->extension = null;
        $this->video->mime = null;
        $this->video->status = 'encoding';
        $this->video->save();

        if(empty($this->video->viddler_id)){
            Log::critical('Viddler did not return a video id!',[$response]);
            throw new ViddlerException('Viddler did not return a video id!');
        }

        return $this->video;
    }
}
