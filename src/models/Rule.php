<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Rule extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $enabled;
    public $settings;
    public $options;

    public $dateCreated;
    public $dateUpdated;

}
