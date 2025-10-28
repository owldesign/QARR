<?php

namespace owldesign\qarr\records;

use owldesign\qarr\elements\Question;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property-read ActiveQueryInterface $answer
 */
class AnswerComment extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
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
        return self::hasOne(Question::class, ['id' => 'id']);
    }

}
