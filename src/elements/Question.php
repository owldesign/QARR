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
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\commerce\Plugin as CommercePlugin;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;

use craft\models\Section;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\QuestionQuery;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\actions\Delete;
use owldesign\qarr\records\Question as QuestionRecord;
use owldesign\qarr\jobs\GeolocationTask;
use owldesign\qarr\jobs\RulesTask;

use yii\base\Exception;
use yii\base\InvalidConfigException;
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
    public $hasPurchased;
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
    /**
     * @var
     */
    public $sectionId;
    /**
     * @var
     */
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

    /**
     * @var Element|null
     */
    private $_element;

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
        } else {
            $sections = Craft::$app->getSections()->getAllSections();
        }

        $sectionIds = [];
        $singleSectionIds = [];
        $sectionsByType = [];

        foreach ($sections as $section) {
            $sectionIds[] = $section->id;

            if ($section->type == Section::TYPE_SINGLE) {
                $singleSectionIds[] = $section->id;
            } else {
                $sectionsByType[$section->type][] = $section;
            }
        }

        $sources = [
            ['heading' => QARR::t('Sources')],
            [
                'id' => '*',
                'key' => '*',
                'type' => '*',
                'label' => Craft::t('app', 'All entries'),
                'defaultSort' => ['postDate', 'desc']
            ]
        ];

        // Singles
        if (!empty($singleSectionIds)) {
            $sources[] = [
                'id' => $singleSectionIds,
                'key' => 'singles',
                'label' => Craft::t('app', 'Singles'),
                'type' => 'sectionId',
                'criteria' => [
                    'sectionId' => $singleSectionIds,
                ],
                'defaultSort' => ['title', 'asc']
            ];
        }

        // Sections
        $sectionTypes = [
            Section::TYPE_CHANNEL => Craft::t('app', 'Channels'),
        ];

        foreach ($sectionTypes as $type => $heading) {
            if (!empty($sectionsByType[$type])) {
                $sources[] = ['heading' => $heading];

                foreach ($sectionsByType[$type] as $section) {
                    /** @var Section $section */
                    $source = [
                        'id' => $section->id,
                        'key' => 'section:' . $section->uid,
                        'label' => Craft::t('site', $section->name),
                        'sites' => $section->getSiteIds(),
                        'type' => 'sectionId',
                        'criteria' => [
                            'sectionId' => $section->id,
                        ]
                    ];

                    $sources[] = $source;
                }
            }
        }

        // Products
        // TODO: Add a check if commerce plugin is installed
        $productTypes = CommercePlugin::getInstance()->productTypes->getAllProductTypes();
        $sources[] = ['heading' => QARR::t('Products')];

        foreach ($productTypes as $productType) {
            $key = 'type:' . $productType->uid;

            $sources[$key] = [
                'id' => $productType->id,
                'key' => $key,
                'label' => $productType->name,
                'type' => 'productTypeId',
                'criteria' => [
                    'productTypeId' => $productType->id
                ]
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

    public function setEagerLoadedElements(string $handle, array $elements)
    {
        if ($handle === 'element') {
            $element = $elements[0] ?? null;
            $this->setElement($element);
        } else {
            parent::setEagerLoadedElements($handle, $elements); // TODO: Change the autogenerated stub
        }
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch($attribute) {
            case 'questionInfo':
                $avatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim( $this->emailAddress)));
                $variables = [
                    'type' => 'question',
                    'author' => [
                        'fullName' => $this->fullName,
                        'emailAddress' => $this->emailAddress,
                        'avatarUrl' => $avatarUrl,
                        'user' => $this->user
                    ],
                    'geolocation' => Json::decode($this->geolocation),
                    'status' => $this->status
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-info', $variables) : $this->title;
                break;
            case 'questionDetails':
                $variables = [
                    'type' => 'question',
                    'element' => $this->element,
                    'feedback' => $this->question,
                    'datePosted' => $this->dateCreated,
                    'reply' => $this->answers,
                    'flags' => $this->flags,
                    'abuse' => $this->abuse,
                    'entryUrl' => $this->url,
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-details', $variables) : $this->title;
                break;
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
//            case 'productId':
//                $product = CommercePlugin::getInstance()->products->getProductById($this->productId);
//                if (!$product) {
//                    return '<p>'.QARR::t('Commerce Plugin is required!').'</p>';
//                }
//                $markup = '<div class="product-wrapper">';
//                $markup .= '<div class="product-badge-wrapper">';
//                $markup .= '<div class="product-badge purple"><span>'.StringHelper::first($product->getType()->name, 1).'</span></div>';
//                $markup .= '</div>';
//                $markup .= '<div class="product-meta">';
//                $markup .= '<span class="product-name">'.$product->title.'</span><span class="product-type">'.$product->getType()->name.'</span>';
//                $markup .= '</div>';
//                $markup .= '</div">';
//                return $markup;
//                break;
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

    /**
     * @return array
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'qarr_questions.status' => QARR::t('Status'),
            'qarr_questions.dateCreated' => QARR::t('Submitted')
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
        $attributes['questionInfo'] = ['label' => QARR::t('Question Info')];
        $attributes['questionDetails'] = ['label' => QARR::t('Question Details')];
//        $attributes['reports'] = ['label' => QARR::t('Reports')];
//        $attributes['guest'] = ['label' => QARR::t('Guest')];
//        $attributes['question'] = ['label' => QARR::t('Question')];
//        $attributes['answer'] = ['label' => QARR::t('Answer')];
//        $attributes['elementId'] = ['label' => QARR::t('Element')];
//        $attributes['dateCreated'] = ['label' => QARR::t('Submitted')];

        return $attributes;
    }

    /**
     * @param string $source
     * @return array
     */
    public static function defaultTableAttributes(string $source): array
    {
        return ['status', 'questionInfo', 'questionDetails'];
    }

//    /**
//     * @return \craft\commerce\elements\Product|null|string
//     */
//    public function product()
//    {
//        $product = CommercePlugin::getInstance()->products->getProductById($this->productId);
//
//        if (!$product) {
//            $product = new Product();
//            return $product;
//            return '<p>'.QARR::t('Commerce Plugin is required!').'</p>';
//        }
//
//        return $product;
//    }

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
        if ($this->_element !== null) {
            return $this->_element;
        }

        if ($this->elementId === null) {
            return null;
        }

        if (($this->_element = Craft::$app->getElements()->getElementById($this->elementId)) === null) {
            throw new InvalidConfigException('Invalid element ID: ' . $this->elementId);
        }

        return $this->_element;
    }

    public function setElement(Element $element = null)
    {
        $this->_element = $element;
    }

    public function getUser()
    {
        return Craft::$app->getUsers()->getUserByUsernameOrEmail($this->emailAddress);
    }

    public function getElementType()
    {
        $class = get_class($this->element);

        if ($class == 'craft\commerce\elements\Product') {
            return 'product';
        }

        if ($class == 'craft\elements\Entry') {
            $section = $this->element->section->type;

            return $section;
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
