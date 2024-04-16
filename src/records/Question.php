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
 * @property-read ActiveQueryInterface $element
 */
class Question extends ActiveRecord
{
    use SoftDeleteTrait;

    // Public Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%qarr_questions}}';
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
}
