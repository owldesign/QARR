<?php

namespace owldesign\qarr\records;

use craft\records\User;
use owldesign\qarr\elements\Review;

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

    /**
     * Return author
     *
     * @return ActiveQueryInterface
     */
    public function getAuthor(): ActiveQueryInterface
    {
        return $this->hasOne(User::class, ['id' => 'authorId']);
    }
}
