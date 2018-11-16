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

use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\QuestionQuery;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\actions\Delete;
use owldesign\qarr\records\Question as QuestionRecord;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

use craft\commerce\Plugin as CommercePlugin;
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
    public $productId;
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
        $sources = [
            [
                'key'   => '*',
                'label' => QARR::t('All Product Types')
            ]
        ];

        $productTypes = CommercePlugin::getInstance()->productTypes->getAllProductTypes();

        foreach ($productTypes as $type) {
            $key = 'type:' . $type->id;
            $sources[$key] = [
                'key'      => $key,
                'label'    => $type->name,
                'criteria' => ['productTypeId' => $type->id]
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
                $markup .= '<div class="badge-wrapper"><div class="entry-badge pending"><span>'.StringHelper::first($this->fullName, 1).'</span></div></div>';
                $markup .= '<div class="guest-meta"><span class="guest-name">'.$this->fullName.'</span><span class="guest-email">'.$this->emailAddress.'</span></div>';
                $markup .= '</div>';
                return $markup;
                break;
            case 'productId':
                $product = CommercePlugin::getInstance()->products->getProductById($this->productId);
                if (!$product) {
                    return '<p>'.QARR::t('Commerce Plugin is required!').'</p>';
                }
                $markup = '<div class="product-wrapper">';
                $markup .= '<div class="product-badge-wrapper">';
                $markup .= '<div class="product-badge purple"><span>'.StringHelper::first($product->getType()->name, 1).'</span></div>';
                $markup .= '</div>';
                $markup .= '<div class="product-meta">';
                $markup .= '<span class="product-name">'.$product->title.'</span><span class="product-type">'.$product->getType()->name.'</span>';
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
        $attributes['reports'] = ['label' => QARR::t('Reports')];
        $attributes['guest'] = ['label' => QARR::t('Guest')];
        $attributes['question'] = ['label' => QARR::t('Question')];
        $attributes['answer'] = ['label' => QARR::t('Answer')];
        $attributes['productId'] = ['label' => QARR::t('Product')];
        $attributes['dateCreated'] = ['label' => QARR::t('Submitted')];

        return $attributes;
    }

    /**
     * @param string $source
     * @return array
     */
    public static function defaultTableAttributes(string $source): array
    {
        return ['status', 'guest', 'question', 'productId', 'dateCreated'];
    }

    /**
     * @return \craft\commerce\elements\Product|null|string
     */
    public function product()
    {
        $product = CommercePlugin::getInstance()->products->getProductById($this->productId);

        if (!$product) {
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
        $record->productId      = $this->productId;
        $record->productTypeId  = $this->productTypeId;
        $record->ipAddress      = $this->ipAddress;
        $record->userAgent      = $this->userAgent;

        $record->save(false);

        if ($isNew) {
            // Profanity Rule
            $checkProfanity = QARR::$plugin->rules->checkProfanity($this->question, $record);
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
