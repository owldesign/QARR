<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\User;
use craft\validators\HandleValidator;

use owldesign\qarr\records\Rule as RuleRecord;

class DirectLink extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $title;
    public $elementId;
    public $userId;
    public $type;
    public $link;
    public $enabled;
    public $completed;
    public $settings;
    public $options;

    public $dateCreated;
    public $dateUpdated;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->title ?: ((string)$this->title ?: static::class);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'elementId', 'userId', 'type', 'link'], 'required']
        ];
    }

    /**
     * Get author of the reply
     *
     * @return User|null
     */
    public function getUser()
    {
        return Craft::$app->users->getUserById($this->userId);
    }

    /**
     * Get author of the reply
     *
     * @return ElementInterface
     */
    public function getElement()
    {
        return Craft::$app->elements->getElementById($this->elementId);
    }

}
