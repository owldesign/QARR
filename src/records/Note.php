<?php

namespace owldesign\qarr\records;

use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class Note extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_notes}}';
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
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getQuestion(): ActiveQueryInterface
    {
        return $this->hasOne(Question::class, ['id' => 'id']);
    }

}
