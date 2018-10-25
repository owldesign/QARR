<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Reply extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $reply;
    public $elementId;
    public $authorId;
    public $dateCreated;
    public $dateUpdated;
    public $author;

    /**
     * Get author of the reply
     *
     * @return \craft\elements\User|null
     */
    public function getAuthor()
    {
        return Craft::$app->users->getUserById($this->author->id);
    }
}
