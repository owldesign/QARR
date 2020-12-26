<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Flagged extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $ruleId;
    public $elementId;
    public $details;
    public $dateCreated;
    public $dateUpdated;

    public $rule;
}
