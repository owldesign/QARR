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

use craft\helpers\Json;
use craft\models\Section;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\ReviewQuery;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\actions\Delete;
use owldesign\qarr\records\Review as ReviewRecord;
use owldesign\qarr\jobs\GeolocationTask;
use owldesign\qarr\jobs\RulesTask;

use Craft;
use craft\base\Element;
use craft\helpers\UrlHelper;
use craft\elements\db\ElementQueryInterface;

use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\elements\Order as CommerceOrder;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\validators\EmailValidator;

/**
 * Class Review
 * @package owldesign\qarr\elements
 */
class Review extends Element
{
    // Constants
    // =========================================================================

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

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
    public $rating;
    /**
     * @var
     */
    public $feedback;
    /**
     * @var
     */
    public $response;
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
    public $display;
    /**
     * @var
     */
    public $displayId;
    /**
     * @var
     */
    public $displayHandle;
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
        return QARR::t('Review');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'qarrReview';
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
        return new ReviewQuery(get_called_class());
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setScenario(self::SCENARIO_LIVE);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $names = parent::extraFields();
        $names[] = 'element';
        return $names;
    }


    /**
     * @inheritdoc
     */
    public function fieldLayoutFields(): array
    {
        $display = $this->getDisplay();

        if ($display) {
            $fieldLayout = $display->getFieldLayout();

            if ($fieldLayout) {
                return $fieldLayout->getFields();
            }
        }

        return [];
    }

    /**
     * Returns the field context this element's content uses.
     *
     * @access protected
     * @return string
     */
    public function getFieldContext(): string
    {
        return 'global';
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        if ($this->displayId !== null) {
            $display = $this->getDisplay();
            if ($display) {
                return $display->getFieldLayout();
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getDisplay()
    {
        if ($this->displayId !== null) {
            $this->display = QARR::$plugin->displays->getDisplayById($this->displayId);
        }

        return $this->display;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'qarr/reviews/' . $this->id
        );
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return UrlHelper::cpUrl(
            'qarr/reviews/' . $this->id
        );
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => QARR::t('Pending'),
            self::STATUS_APPROVED => QARR::t('Approved'),
            self::STATUS_REJECTED => QARR::t('Rejected'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['fullName', 'emailAddress', 'rating', 'feedback'], 'required'];
        $rules[] = [['emailAddress'], EmailValidator::class];

        return $rules;
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

        // Craft Commerce
        $commerce = Craft::$app->getPlugins()->isPluginEnabled('commerce');
        if ($commerce) {
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
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions[] = SetStatus::class;
        $actions[] = Delete::class;

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        if ($handle === 'element') {
            $element = $elements[0] ?? null;
            $this->setElement($element);
        } else {
            parent::setEagerLoadedElements($handle, $elements); // TODO: Change the autogenerated stub
        }
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'author':
                $variables = [
                    'type' => 'reviews',
                    'entry' => $this,
                    'author' => [
                        'fullName' => $this->fullName,
                        'emailAddress' => $this->emailAddress,
                        'avatarUrl' => $this->avatarUrl(),
                        'user' => $this->user
                    ],
                    'geolocation' => Json::decode($this->geolocation),
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-author', $variables) : $this->title;
                break;
            case 'stars':
                $variables = [
                    'type' => 'reviews',
                    'entry' => $this,
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-stars', $variables) : $this->title;
                break;
//            case 'status':
//                $variables = [
//                    'entry' => $this,
//                ];
//                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-status', $variables) : $this->title;
//                break;
            case 'location':
                $variables = [
                    'entry' => $this,
                    'geolocation' => Json::decode($this->geolocation),
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-location', $variables) : $this->title;
                break;
            case 'feedback':
                $variables = [
                    'type' => 'reviews',
                    'entry' => $this,
                    'element' => $this->element,
                    'reply' => $this->reply,
                    'feedback' => $this->feedback,
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-feedback', $variables) : $this->title;
                break;
            case 'element':
                $variables = [
                    'type' => 'reviews',
                    'entry' => $this,
                ];
                return $variables ? Craft::$app->getView()->renderTemplate('qarr/_elements/element-element', $variables) : $this->title;
                break;
            default:
                return parent::tableAttributeHtml($attribute);
                break;
        }
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'status' => QARR::t('Status'),
            'rating' => QARR::t('Rating'),
            'fullName' => QARR::t('Author'),
            'dateCreated' => QARR::t('Submitted')
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [];

        $attributes['title'] = ['label' => QARR::t('Status')];
        $attributes['author'] = ['label' => QARR::t('Author')];
        $attributes['feedback'] = ['label' => QARR::t('Feedback')];
        $attributes['stars'] = ['label' => QARR::t('Stars')];
//        $attributes['status'] = ['label' => QARR::t('Status')];
        $attributes['location'] = ['label' => QARR::t('Location')];
        $attributes['element'] = ['label' => QARR::t('Element')];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public static function defaultTableAttributes(string $source): array
    {
        return ['title', 'stars', 'feedback', 'element'];
    }

    /**
     * Entry reply
     *
     * @return mixed
     */
    public function getReply()
    {
        return QARR::$plugin->replies->getReply($this->id);
    }

    /**
     * Entry customer
     *
     * @return mixed
     */
    public function getCustomer()
    {
        if ($this->commerce) {
            $customer = null;
            $user = Craft::$app->users->getUserByUsernameOrEmail($this->emailAddress);

            if ($user) {
                $customer = CommercePlugin::getInstance()->customers->getCustomerByUserid($user->id);
            }

            return $customer;
        } else {
            return false;
        }
    }

    /**
     * Element type
     *
     * @return string
     */
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

    /**
     * Entry element
     *
     * @return Element|\craft\base\ElementInterface|null
     * @throws InvalidConfigException
     */
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

    /**
     * Set entry element
     *
     * @param Element|null $element
     */
    public function setElement(Element $element = null)
    {
        $this->_element = $element;
    }

    /**
     * Entry user
     *
     * @return \craft\elements\User|null
     */
    public function getUser()
    {
        return Craft::$app->getUsers()->getUserByUsernameOrEmail($this->emailAddress);
    }

    /**
     * Entry author
     *
     * @return \craft\elements\User|null
     */
    public function getAuthor()
    {
        return Craft::$app->getUsers()->getUserByUsernameOrEmail($this->emailAddress);
    }

    public function hasPurchased()
    {
        $commerce = $this->getCommerce();

        if (!$commerce) {
            return false;
        }

        if ($this->getElementType() != 'product') {
            return false;
        }

        $product = CommercePlugin::getInstance()->getProducts()->getProductById($this->elementId);

        $order = CommerceOrder::find()
            ->email($this->emailAddress)
            ->hasPurchasables($product->defaultVariant)
            ->one();

        return $order;
    }

    /**
     * @param $time
     * @return string
     */
    public function getTimeAgo($time)
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $difference = $now - strtotime($time);

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return "$difference $periods[$j]";
    }

    /**
     * Get entry flags
     *
     * @return mixed
     */
    public function getFlags()
    {
        $result = QARR::$plugin->rules->getFlagged($this->id);

        return $result;
    }

    /**
     * Get author avatar url from gravatar.com
     *
     * @return string
     */
    public function avatarUrl()
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->emailAddress)));
    }

    /**
     * Get settings
     *
     * @return mixed
     */
    public function getSettings()
    {
        return QARR::$plugin->settings;
    }

    /**
     * Check for commerce plugin
     *
     * @return bool
     */
    public function getCommerce()
    {
        $commerce = Craft::$app->getPlugins()->isPluginEnabled('commerce');

        return $commerce;
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
            $record = ReviewRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Review ID: ' . $this->id);
            }
        } else {
            $record = new ReviewRecord();
            $record->id = $this->id;
        }

        $record->fullName = $this->fullName;
        $record->emailAddress = $this->emailAddress;
        $record->feedback = $this->feedback;
        $record->rating = $this->rating;
        $record->status = $this->status;
        $record->options = $this->options;
        $record->displayId = $this->displayId;
        $record->elementId = $this->elementId;
        $record->sectionId = $this->sectionId;
        $record->productTypeId = $this->productTypeId;
        $record->ipAddress = $this->ipAddress;
        $record->userAgent = $this->userAgent;

        $record->save(false);

        if ($isNew) {
            // Apply Rule
            Craft::$app->getQueue()->push(new RulesTask([
                'entry' => $record->toArray(),
                'elementType' => 'review'
            ]));

            // Apply Geolocation
            Craft::$app->getQueue()->push(new GeolocationTask([
                'ipAddress' => $this->ipAddress,
                'elementId' => $this->id,
                'table' => '{{%qarr_reviews}}'
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
        $record = ReviewRecord::findOne($this->id);
        $record->softDelete();

        // Remove rules
        QARR::$plugin->getRules()->removeRecord($this->id);
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
