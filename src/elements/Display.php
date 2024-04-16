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
use craft\fields\data\SingleOptionFieldData;
use craft\models\FieldLayout;
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
 *
 * @property-read SingleOptionFieldData $emptyOptionFieldData
 * @property-read array $fields
 */
class Display extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $name;
    /**
     * @var string
     */
    public string $handle;
    /**
     * @var string
     */
    public string $oldHandle;
    /**
     * @var string
     */
    public ?int $fieldLayoutId = null;
    /**
     * @var string
     */
    public string $titleFormat;
    /**
     * @var int
     */
    public int $oldFieldLayoutId;
    /**
     * @var
     */
    public $settings;
    /**
     * @var
     */
    public $options;
    /**
     * @vars string
     */
    public string $customTemplates;

    /**
     * @inheritdoc
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
    public static function refHandle(): ?string
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
    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl(
            'qarr/displays/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        return $this->getBehavior('fieldLayout')->getFieldLayout();
    }

    /**
     * @inheritdoc
     *
     * @return DisplayQuery The newly created [[DisplayQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new DisplayQuery(static::class);
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
        return [
            'name' => QARR::t('Display Name'),
            'elements.dateCreated' => QARR::t('Date Created'),
            'elements.dateUpdated' => QARR::t('Date Updated'),
        ];
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
        return ['name', 'handle', 'totalSubmissions'];
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
                    return (new Query())
                        ->select('COUNT(*)')
                        ->from('{{%qarr_reviews}}')
                        ->where(['displayId' => $this->id])
                        ->scalar();
                }
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key'   => '*',
                'label' => QARR::t('All Displays')
            ]
        ];
    }

    /**
     * Returns the fields associated with this form.
     *
     * @return array
     */
    public function getFields(): array
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
    public function getField(string $handle): ?FieldInterface
    {
        $fields = $this->getFields();

        if (is_string($handle) && !empty($handle)) {
            return $fields[$handle] ?? null;
        }

        return null;
    }

    /**
     * Return empty option field data
     *
     * @return SingleOptionFieldData
     */
    public function getEmptyOptionFieldData(): SingleOptionFieldData
    {
        return new SingleOptionFieldData(null, null, true, true);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
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
    public function afterSave(bool $isNew): void
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

    public function afterDelete(): void
    {
        // Delete display record
        QARR::$plugin->getDisplays()->deleteDisplayById($this->id);

        if ($this->fieldLayoutId !== null) {
            Craft::$app->getFields()->deleteLayoutById($this->fieldLayoutId);
        }

        $this->beforeDelete();
    }
}
