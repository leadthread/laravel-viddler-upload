<?php

namespace LeadThread\Viddler\Upload\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use LeadThread\Viddler\Upload\Models\Viddler;

class ViddlerProgress implements ShouldBroadcast
{
    use SerializesModels;

    public $model;

    /**
     * Create a new event instance.
     *
     * @param  Viddler  $model
     * @return void
     */
    public function __construct(Viddler $model)
    {
        $this->model = $model;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return string[]
     */
    public function broadcastOn()
    {
        return ['viddler.'.$this->model->id];
    }
}
