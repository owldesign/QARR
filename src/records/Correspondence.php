<?php

namespace owldesign\qarr\records;

use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property-read ActiveQueryInterface $question
 * @property-read ActiveQueryInterface $review
 */
class Correspondence extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%qarr_correspondence}}';
    }

    /**
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getReview(): ActiveQueryInterface
    {
        return self::hasOne(Review::class, ['id' => 'elementId']);
    }

    /**
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getQuestion(): ActiveQueryInterface
    {
        return self::hasOne(Question::class, ['id' => 'elementId']);
    }

}
