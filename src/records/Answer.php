<?php

namespace owldesign\qarr\records;

//use owldesign\qarr\elements\Review;
//use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use craft\elements\User;
use yii\db\ActiveQueryInterface;

class Answer extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_questions_answers}}';
    }

    /**
     * Return entry
     *
     * @return ActiveQueryInterface
     */
    public function getQuestion(): ActiveQueryInterface
    {
        return $this->hasOne(Question::class, ['id' => 'elementId']);
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
