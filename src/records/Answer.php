<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;
use craft\elements\User;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property-read ActiveQueryInterface $question
 * @property-read ActiveQueryInterface $author
 */
class Answer extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
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
        return self::hasOne(Question::class, ['id' => 'elementId']);
    }

    /**
     * Return author
     *
     * @return ActiveQueryInterface
     */
    public function getAuthor(): ActiveQueryInterface
    {
        return self::hasOne(User::class, ['id' => 'authorId']);
    }

}
