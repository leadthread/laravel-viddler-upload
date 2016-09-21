<?php

namespace Zenapply\Viddler\Upload\Events;

use Zenapply\Viddler\Upload\Models\Viddler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ViddlerFinished implements ShouldBroadcast
{
    use SerializesModels;

    public $model;

    /**
     * Create a new event instance.
     *
     * @param  Podcast  $model
     * @return void
     */
    public function __construct(Viddler $model)
    {
        $this->model = $model;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['viddler.'.$this->model->id];
    }
}
