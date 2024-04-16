<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

use craft\elements\User;
use owldesign\qarr\QARR;

class Answer extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $answer;
    public $elementId;
    public $anonymous;
    public $authorId;
    public $status;
    public $abuse;
    public $isHelpful;
    public $dateCreated;
    public $dateUpdated;

    public function __toString()
    {
        return $this->answer ?: ((string)$this->answer ?: static::class);
    }

    /**
     * Get author of the reply
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return Craft::$app->users->getUserById($this->authorId);
    }

    /**
     * Get answers question
     *
     * @return mixed
     */
    public function getQuestion(): mixed
    {
        return QARR::$plugin->elements->getElement('questions', $this->elementId);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['answer', 'elementId', 'authorId'], 'required'],
        ];
    }
}
