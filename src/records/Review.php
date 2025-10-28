<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\records;

use craft\records\Element;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property-read ActiveQueryInterface $response
 * @property-read ActiveQueryInterface $element
 */
class Review extends ActiveRecord
{
    use SoftDeleteTrait;

    // Public Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%qarr_reviews}}';
    }

    /**
     * Get element
     *
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return self::hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * Get response
     *
     * @return ActiveQueryInterface
     */
    public function getResponse(): ActiveQueryInterface
    {
        return self::hasOne(Reply::class, ['id' => 'elementId']);
    }
}
