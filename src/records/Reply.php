<?php

namespace owldesign\qarr\records;

use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class Reply extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_reviews_replies}}';
    }

    /**
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getReview(): ActiveQueryInterface
    {
        return $this->hasOne(Review::class, ['id' => 'id']);
    }
}
