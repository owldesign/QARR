<?php

namespace owldesign\qarr\records;

use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class AnswerComment extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_questions_answers_comments}}';
    }

    /**
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getAnswer(): ActiveQueryInterface
    {
        return $this->hasOne(Question::class, ['id' => 'id']);
    }

}
