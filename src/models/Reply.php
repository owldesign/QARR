<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Reply extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $uid;
    public $reply;
    public $elementId;
    public $authorId;
    public $dateCreated;
    public $dateUpdated;

    public function __toString()
    {
        return $this->reply ?: ((string)$this->reply ?: static::class);
    }

    /**
     * Get author of the reply
     *
     */
    public function getAuthor(): ?\craft\elements\User
    {
        return Craft::$app->getusers()->getUserById($this->authorId);
    }
}
