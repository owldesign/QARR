<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

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

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->answer ?: ((string)$this->answer ?: static::class);
    }

    /**
     * Get author of the reply
     *
     * @return \craft\elements\User|null
     */
    public function getAuthor()
    {
        return Craft::$app->users->getUserById($this->authorId);
    }

    /**
     * Get answers question
     *
     * @return mixed
     */
    public function getQuestion()
    {
        return QARR::$plugin->elements->getElement('questions', $this->elementId);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['answer', 'elementId', 'authorId'], 'required'],
        ];
    }
}
