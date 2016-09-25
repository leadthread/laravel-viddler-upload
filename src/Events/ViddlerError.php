<?php

namespace Zenapply\Viddler\Upload\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Zenapply\Viddler\Upload\Models\Viddler;

class ViddlerError implements ShouldBroadcast
{
    use SerializesModels;

    public $model;
    public $error;

    /**
     * Create a new event instance.
     *
     * @param  Viddler  $model
     * @return void
     */
    public function __construct(Viddler $model, $error)
    {
        $this->model = $model;
        $this->error = $error;
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
