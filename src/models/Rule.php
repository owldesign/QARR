<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;
use craft\validators\HandleValidator;

use owldesign\qarr\records\Rule as RuleRecord;

class Rule extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $enabled;
    public $data;
    public $icon;
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
            [['name'], 'unique', 'targetAttribute' => ['name']],
            [['handle'], 'unique', 'targetAttribute' => ['handle']],
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
            [['handle'], HandleValidator::class, 'reservedWords' => [
                'id',
                'dateCreated',
                'dateUpdated',
                'uid',
                'title'
            ]
            ],
        ];
    }

}
