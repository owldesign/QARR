<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Correspondence extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $email;
    public $subject;
    public $response;
    public $allowReplies;
    public $password;
    public $ownerEmail;
    public $type;
    public $elementId;
    public $dateCreated;
    public $dateUpdated;
    public $author;

}
