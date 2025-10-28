<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use craft\behaviors\FieldLayoutBehavior;

use owldesign\qarr\records\Display as DisplayRecord;

class Display extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $type;
    public $fieldLayoutId;
    public $titleFormat;
    public $enabled;
    public $options;
    public $settings;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['name', 'handle'], UniqueValidator::class, 'targetClass' => DisplayRecord::class],
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return mixed
     */
    public function getFieldLayout()
    {
        return $this->getBehavior('fieldLayout')->getFieldLayout();
    }
}
