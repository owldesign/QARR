<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;
use craft\elements\User;

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
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return Craft::$app->users->getUserById($this->authorId);
    }
}
