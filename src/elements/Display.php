<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\elements;

use Craft;
use craft\base\Element;
use craft\base\FieldInterface;
use craft\elements\db\ElementQueryInterface;
use yii\base\Exception;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use craft\behaviors\FieldLayoutBehavior;

use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\DisplayQuery;
use owldesign\qarr\elements\actions\Delete;
use owldesign\qarr\records\Display as DisplayRecord;


/**
 * Class Display
 * @package owldesign\qarr\elements
 */
class Display extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $handle;
    /**
     * @var
     */
    public $oldHandle;
    /**
     * @var
     */
    public $fieldLayoutId;
    /**
     * @var
     */
    public $titleFormat;
    /**
     * @var
     */
    public $oldFieldLayoutId;
    /**
     * @var
     */
    public $settings;
    /**
     * @var
     */
    public $options;
    /**
     * @var
     */
    public $customTemplates;

    /**
     * @inheritdoc
     */
    public function behaviors()
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
    public function getFieldContext(): string
    {
        return 'global';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Display';
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'qarrDisplay';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED    => QARR::t('Enabled'),
            self::STATUS_DISABLED   => QARR::t('Disabled'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'qarr/displays/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        $behavior = $this->getBehavior('fieldLayout');

        return $behavior->getFieldLayout();
    }

    /**
     * @inheritdoc
     *
     * @return DisplayQuery The newly created [[DisplayQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new DisplayQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];
        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
        ]);
        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'handle'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'name' => QARR::t('Display Name'),
            'elements.dateCreated' => QARR::t('Date Created'),
            'elements.dateUpdated' => QARR::t('Date Updated'),
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes['name'] = ['label' => QARR::t('Name')];
        $attributes['handle'] = ['label' => QARR::t('Handle')];
        $attributes['totalSubmissions'] = ['label' => QARR::t('Total Submissions')];

        return $attributes;
    }

    /**
     * @param string $source
     * @return array
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['name', 'handle', 'totalSubmissions'];
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'handle':
                {
                    return '<code>'.$this->handle.'</code>';
                }
            case 'totalSubmissions':
                {
                    $totalSubmissions = (new Query())
                        ->select('COUNT(*)')
                        ->from('{{%qarr_reviews}}')
                        ->where(['displayId' => $this->id])
                        ->scalar();

                    return $totalSubmissions;
                }
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key'   => '*',
                'label' => QARR::t('All Displays')
            ]
        ];

        return $sources;
    }

    /**
     * Returns the fields associated with this form.
     *
     * @return array
     */
    public function getFields()
    {
        if ($this->_fields === null) {
            $this->_fields = [];
            $fields = $this->getFieldLayout()->getFields();

            foreach ($fields as $field) {
                $this->_fields[$field->handle] = $field;
            }
        }

        return $this->_fields;
    }

    /**
     * @param string $handle
     *
     * @return null|FieldInterface
     */
    public function getField($handle)
    {
        $fields = $this->getFields();

        if (is_string($handle) && !empty($handle)) {
            return $fields[$handle] ?? null;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules      = parent::rules();
        $rules[]    = [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true];
        $rules[]    = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
        $rules[]    = [['name', 'handle'], UniqueValidator::class, 'targetClass' => DisplayRecord::class];
        $rules[]    = [['name', 'handle'], 'required'];
        $rules[]    = [['name', 'handle'], 'string', 'max' => 255];

        return $rules;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @param bool $isNew
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $record = DisplayRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Display ID: '.$this->id);
            }
        } else {
            $record = new DisplayRecord();
            $record->id = $this->id;
        }

        $record->name = $this->name;
        $record->handle = $this->handle;
        $record->enabled = $this->enabled;
        $record->titleFormat = $this->titleFormat;
        $record->fieldLayoutId = $this->fieldLayoutId;
        $record->options = Json::encode($this->options);
        $record->settings = Json::encode($this->settings);

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @return bool
     */
    public function afterDelete(): bool
    {
        // Delete display record
        QARR::$plugin->getDisplays()->deleteDisplayById($this->id);

        if ($this->fieldLayoutId !== null) {
            Craft::$app->getFields()->deleteLayoutById($this->fieldLayoutId);
        }
        return parent::beforeDelete();
    }
}
