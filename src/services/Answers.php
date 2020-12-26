<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\services;

use owldesign\qarr\QARR;
use owldesign\qarr\models\Answer;
use owldesign\qarr\records\Answer as AnswerRecord;


use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use yii\base\Exception;

/**
 * Class Answers
 * @package owldesign\qarr\services
 */
class Answers extends Component
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @param $status
     * @param $elementId
     * @return array|null
     */
    public function getAnswers($status, $elementId)
    {
        $answers = [];

        $query = AnswerRecord::find()
            ->where(['elementId' => $elementId])
            ->orderBy(['status' => 'approved']);

        if ($status !== '*') {
            $query->andWhere(['status' => $status]);
        }

        $records = $query->all();

        if (!$records) {
            return null;
        }

        foreach($records as $key => $record) {
            $answers[$key] = new Answer($record->toArray(['id', 'answer', 'elementId', 'anonymous', 'authorId', 'status', 'abuse', 'isHelpful', 'dateCreated', 'dateUpdated']));
        }

        return $answers;
    }

    /**
     * @param int $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getAnswerById(int $id)
    {
        if (!$id) {
            return null;
        }

        $record = AnswerRecord::find()
            ->where(['id' => $id])
            ->one();

        return $record;
    }

    /**
     * @param Answer $answer
     * @param $author
     * @return bool|null|AnswerRecord
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function save(Answer $answer, $author)
    {
        $isNewAnswer = !$answer->id;

        if (!$isNewAnswer) {
            $record = AnswerRecord::findOne($answer->id);

            if (!$record) {
                throw new Exception(QARR::t('No answer exists with the ID “{id}”', ['id' => $answer->id]));
            }
        } else {
            $record = new AnswerRecord();
        }

        $record->validate();

        if ($record->hasErrors()) {
            return false;
        }

        $record->answer     = $answer->answer;
        $record->elementId  = $answer->elementId;
        $record->authorId   = $author->id;
        $record->anonymous  = $answer->anonymous;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $record->save(false);
            $answer->id = $record->id;

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $record;
    }

    /**
     * @param $id
     * @return bool|false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $record = AnswerRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$record) {
            return true;
        }

        return $record->delete();
    }

    /**
     * @param $element
     * @return bool|false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteAnswersByElement($element)
    {
        $record = AnswerRecord::find()
            ->where(['elementId' => $element->id])
            ->one();

        if (!$record) {
            return true;
        }

        return $record->delete();
    }

    // Private Methods
    // =========================================================================


}
