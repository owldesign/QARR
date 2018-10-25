<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Note extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $note;
    public $elementId;
    public $authorId;
    public $dateCreated;

    public $author;

    /**
     * Get author of the reply
     *
     * @return \craft\elements\User|null
     */
    public function getAuthor()
    {
        return Craft::$app->users->getUserById($this->authorId);
    }
}
