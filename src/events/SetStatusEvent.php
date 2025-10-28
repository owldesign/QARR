<?php

namespace owldesign\qarr\events;

use yii\base\Event;

class SetStatusEvent extends Event
{
    // Properties
    // =========================================================================

    public $response;
    public $status;
    public $type;
}