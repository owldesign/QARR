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
use craft\commerce\elements\Product;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\models\Section;
use craft\commerce\Plugin as CommercePlugin;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;

use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\QuestionQuery;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\actions\Delete;
use owldesign\qarr\records\Question as QuestionRecord;
use owldesign\qarr\jobs\GeolocationTask;
use owldesign\qarr\jobs\RulesTask;

use yii\base\Exception;
use yii\validators\EmailValidator;

/**
 * Class Question
 * @package owldesign\qarr\elements
 */
class Question extends Element
{
    // Constants
    // =========================================================================

    /**
     *
     */
    const STATUS_PENDING    = 'pending';
    /**
     *
     */
    const STATUS_APPROVED   = 'approved';
    /**
     *
     */
    const STATUS_REJECTED   = 'rejected';

    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $fullName;
    /**
     * @var
     */
    public $emailAddress;
    /**
     * @var
     */
    public $question;
    /**
     * @var string
     */
    public $status = 'pending';
    /**
     * @var
     */
    public $options;
    /**
     * @var
     */
    public $answer;
    /**
     * @var
     */
    public $isNew;
    /**
     * @var
     */
    public $abuse;
    /**
     * @var
     */
    public $votes;
    /**
     * @var
     */
    public $displayId;
    /**
     * @var
     */
    public $elementId;
    public $element;
    public $elementType;
    public $elementSource;
    public $sectionId;
    public $structureId;
    public $productTypeId;
    /**
     * @var
     */
    public $geolocation;
    /**
     * @var
     */
    public $ipAddress;
    /**
     * @var
     */
    public $userAgent;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return QARR::t('Question');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'qarrQuestion';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
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
    public function getStatus()
    {
        if ($this->status == 'pending') {
            return self::STATUS_PENDING;
        }

        if ($this->status == 'approved') {
            return self::STATUS_APPROVED;
        }

        if ($this->status == 'rejected') {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_PENDING;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new QuestionQuery(get_called_class());
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->title ?: ((string)$this->id ?: static::class);
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'qarr/questions/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return UrlHelper::cpUrl(
            'qarr/questions/'.$this->id
        );
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING    => QARR::t('Pending'),
            self::STATUS_APPROVED   => QARR::t('Approved'),
            self::STATUS_REJECTED   => QARR::t('Rejected'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullName', 'emailAddress', 'question'], 'required'],
            [['emailAddress'], EmailValidator::class]
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        if ($context === 'index') {
            $sections = Craft::$app->getSections()->getEditableSections();
            $productTypes = CommercePlugin::getInstance()->getProductTypes()->getEditableProductTypes();
        } else {
            $sections = Craft::$app->getSections()->getAllSections();
            $productTypes = CommercePlugin::getInstance()->getProductTypes()->getAllProductTypes();
        }

        $sectionIds = [];
        $singleSectionIds = [];
        $sectionsByType = [];
        $productTypeIds = [];

        foreach ($sections as $section) {
            $sectionIds[] = $section->id;

            if ($section->type == Section::TYPE_SINGLE) {
                $singleSectionIds[] = $section->id;
            } else {
                $sectionsByType[$section->type][] = $section;
            }
        }

        foreach ($productTypes as $productType) {
            $productTypeIds[] = $productType->id;
        }

        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('app', 'All entries'),
                'criteria' => [
                    'parentId' => $sectionIds,
                ],
                'defaultSort' => ['postDate', 'desc']
            ]
        ];



        if (!empty($singleSectionIds)) {
            $sources[] = [
                'key' => 'singles',
                'label' => Craft::t('app', 'Singles'),
                'criteria' => [
                    'parentId' => $singleSectionIds,
                ],
                'defaultSort' => ['title', 'asc']
            ];
        }

        $sectionTypes = [
            Section::TYPE_CHANNEL => Craft::t('app', 'Channels'),
            Section::TYPE_STRUCTURE => Craft::t('app', 'Structures')
        ];

        foreach ($sectionTypes as $type => $heading) {
            if (!empty($sectionsByType[$type])) {
                $sources[] = ['heading' => $heading];

                foreach ($sectionsByType[$type] as $section) {
                    /** @var Section $section */
                    $source = [
                        'key' => 'section:' . $section->uid,
                        'label' => Craft::t('site', $section->name),
                        'sites' => $section->getSiteIds(),
                        'data' => [
                            'type' => $type,
                            'handle' => $section->handle
                        ],
                        'criteria' => [
                            'parentId' => $section->id,
                        ]
                    ];

                    if ($type == Section::TYPE_STRUCTURE) {
                        $source['defaultSort'] = ['structure', 'asc'];
                        $source['structureId'] = $section->structureId;
                        $source['structureEditable'] = Craft::$app->getUser()->checkPermission('publishEntries:' . $section->uid);
                    } else {
                        $source['defaultSort'] = ['postDate', 'desc'];
                    }

                    $sources[] = $source;
                }
            }
        }

        $sources[] = ['heading' => QARR::t('Product Types')];

        foreach ($productTypes as $productType) {
            $key = 'productType:' . $productType->uid;

            $sources[$key] = [
                'key' => $key,
                'label' => $productType->name,
                'data' => [
                    'handle' => $productType->handle,
                ],
                'criteria' => ['elementId' => $productType->id]
            ];
        }

        return $sources;
    }

    /**
     * @param string|null $source
     * @return array
     */
    protected static function defineActions(string $source = null): array
    {
        $actions[] = SetStatus::class;
        $actions[] = Delete::class;

        return $actions;
    }

    /**
     * @param string $attribute
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch($attribute) {
            case 'flags':
                $flags = self::getFlags();
                $markup = '<div class="flags-container">';

                if ($flags) {
                    foreach ($flags as $flag) {
                        $markup .= '<div class="flags-wrapper">';
                        $markup .= '<div class="flagged-item"><i class="fal fa-'. $flag["rule"]["icon"] .'"></i> <span>'. $flag["rule"]["name"] .'</span></div>';
                        $markup .= '</div>';
                    }
                }
                return $markup;
                break;
            case 'reports':
                $markup = '<div class="reports-wrapper">';
                if ($this->abuse) {
                    $markup .= '<div class="badge-wrapper"><i class="fa fa-exclamation-circle"></i></span></div></div>';
                }
                $markup .= '</div>';
                return $markup;
                break;
            case 'guest':
                $markup = '<div class="guest-wrapper">';
                $markup .= '<div class="guest-meta"><span class="guest-name">'.$this->fullName.'</span><span class="guest-email">'.$this->emailAddress.'</span></div>';
                $markup .= '</div>';
                return $markup;
                break;
            case 'elementId':
                $markup = '<div class="element-wrapper">';
                $markup .= '<div class="element-meta">';
                $markup .= '<span class="element-icon"><i class="fal fa-newspaper"></i></span>';
                $markup .= '<span class="element-name">'.$this->element->title.'</span><span class="element-type">'.$this->element->getType()->name.'</span>';
                $markup .= '</div>';
                $markup .= '</div">';
                return $markup;
                break;
            case 'rating':
                $rating = (int)$this->rating;
                $markup = '<div class="rating-wrapper">';
                for ($i = 1; $i <= $rating; $i++) {
                    $markup .= '<span class="qarr-rating-star"><i class="fa fa-star"></i></span>';
                }
                $markup .= '</div>';
                return $markup;
                break;
//            case 'actions':
//                $editUrl = UrlHelper::cpUrl('qarr/reviews/'.$this->id);
//                $markup = '<a href="'.$editUrl.'">View</a>';
//                return $markup;
//                break;
            case 'feedback':
                $markup = '<div class="feedback-wrapper"><span>'.StringHelper::truncateWords($this->feedback, 3).'</span></div>';
                return $markup;
                break;
            case 'dateCreated':
                $date = DateTimeHelper::toIso8601($this->dateCreated);
                $markup = '<div class="date-wrapper"><span>'.$this->getTimeAgo($date) . ' ago</span></div>';
                return $markup;
                break;
            default:
                return parent::tableAttributeHtml($attribute);
                break;
        }
    }

    // TODO: Make thead orderable

    /**
     * @return array
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'qarr_questions.status' => QARR::t('Status'),
            'qarr_questions.elementId' => QARR::t('Element'),
            'element.dateCreated' => QARR::t('Date Submitted'),
        ];

        return $attributes;
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [];
        $attributes['status'] = ['label' => QARR::t('Title')];
        $attributes['reports'] = ['label' => QARR::t('Reports')];
        $attributes['guest'] = ['label' => QARR::t('Guest')];
        $attributes['question'] = ['label' => QARR::t('Question')];
        $attributes['answer'] = ['label' => QARR::t('Answer')];
        $attributes['elementId'] = ['label' => QARR::t('Page')];
        $attributes['dateCreated'] = ['label' => QARR::t('Submitted')];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['status', 'guest', 'question', 'elementId', 'dateCreated'];
    }

    /**
     * @param string $source
     * @return array
     */
    public static function defaultTableAttributes(string $source): array
    {
        return ['status', 'guest', 'question', 'elementId', 'dateCreated'];
    }

    /**
     * @return \craft\commerce\elements\Product|null|string
     */
    public function product()
    {
        $product = CommercePlugin::getInstance()->products->getProductById($this->elementId);

        if (!$product) {
            $product = new Product();
            return $product;
            return '<p>'.QARR::t('Commerce Plugin is required!').'</p>';
        }

        return $product;
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getAnswers($status = '*')
    {
        $response = QARR::$plugin->answers->getAnswers($status, $this->id);

        return $response;
    }

    /**
     * @param $time
     * @return string
     */
    public function getTimeAgo($time)
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();
        $difference     = $now - strtotime($time);

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j]";
    }

    public function getFlags()
    {
        $result = QARR::$plugin->rules->getFlagged($this->id);

        return $result;
    }

    public function getElement()
    {
        return Craft::$app->elements->getElementById($this->elementId);
    }

    public function getElementType()
    {
        switch (true) {
            case $this->element instanceof Entry:
                return 'entry';
                break;
            case $this->element instanceof Category:
                return 'category';
                break;
            case $this->element instanceof Asset:
                return 'asset';
                break;
            case $this->element instanceof Product:
                return 'product';
                break;
        }
    }

    public function getElementSource()
    {
        switch (true) {
            case $this->element instanceof Entry:
                return $this->element->section->type;
                break;
            case $this->element instanceof Category:
                return 'category';
                break;
            case $this->element instanceof Asset:
                return 'asset';
                break;
            case $this->element instanceof Product:
                return $this->element->type->name;
                break;
        }
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @param bool $isNew
     * @return bool
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @param bool $isNew
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $record = QuestionRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Question ID: '.$this->id);
            }
        } else {
            $record = new QuestionRecord();
            $record->id = $this->id;
        }

        $record->fullName       = $this->fullName;
        $record->emailAddress   = $this->emailAddress;
        $record->question       = $this->question;
        $record->status         = $this->status;
        $record->options        = $this->options;
        $record->displayId      = $this->displayId;
        $record->elementId      = $this->elementId;
        $record->sectionId      = $this->sectionId;
        $record->structureId    = $this->structureId;
        $record->productTypeId  = $this->productTypeId;
        $record->ipAddress      = $this->ipAddress;
        $record->userAgent      = $this->userAgent;

        $record->save(false);

        if ($isNew) {
            // Apply Rule
            Craft::$app->getQueue()->push(new RulesTask([
                'entry' => $record,
            ]));

            // Apply Geolocation
            Craft::$app->getQueue()->push(new GeolocationTask([
                'ipAddress' => $this->ipAddress,
                'elementId' => $this->id,
                'table' => '{{%qarr_questions}}'
            ]));
        }

        parent::afterSave($isNew);
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        $record = QuestionRecord::findOne($this->id);
        $record->delete();
    }

    /**
     * @param int $structureId
     * @return bool
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @param int $structureId
     */
    public function afterMoveInStructure(int $structureId)
    {
    }
}
