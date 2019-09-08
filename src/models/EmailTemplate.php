<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\User;
use craft\validators\HandleValidator;

use owldesign\qarr\records\Rule as RuleRecord;

class EmailTemplate extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $enabled;
    public $template;
    public $settings;
    public $options;

    public $dateCreated;
    public $dateUpdated;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->name ?: ((string)$this->name ?: static::class);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required']
        ];
    }
}
