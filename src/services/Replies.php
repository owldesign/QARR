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
use owldesign\qarr\models\Reply;
use owldesign\qarr\records\Reply as ReplyRecord;


use Craft;
use craft\base\Component;
use yii\base\Exception;

/**
 * Class Replies
 * @package owldesign\qarr\services
 */
class Replies extends Component
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Reply
     *
     * @param $elementId
     * @return Reply|null
     */
    public function getReply($elementId)
    {
        $record = ReplyRecord::find()
            ->where(['elementId' => $elementId])
            ->one();

        if (!$record) {
            return null;
        }

        return new Reply($record->toArray());
    }

    /** Reply by id
     *
     * @param int $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getReplyById(int $id)
    {
        if (!$id) {
            return null;
        }

        $record = ReplyRecord::find()
            ->where(['id' => $id])
            ->one();

        return $record;
    }

    /**
     * Reply model by id
     *
     * @param int $id
     * @return null|Reply
     */
    public function getReplyModelById(int $id)
    {
        if (!$id) {
            return null;
        }

        $record = ReplyRecord::find()
            ->where(['id' => $id])
            ->one();

        return new Reply($record);
    }

    /**
     * Save
     *
     * @param Reply $reply
     * @param $author
     * @return bool|null|ReplyRecord
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function save(Reply $reply, $author)
    {
        $isNewReply = !$reply->id;

        if (!$isNewReply) {
            $record = ReplyRecord::findOne($reply->id);

            if (!$record) {
                throw new Exception(QARR::t('No reply exists with the ID “{id}”', ['id' => $reply->id]));
            }
        } else {
            $record = new ReplyRecord();
        }

        $record->validate();

        if ($record->hasErrors()) {
            return false;
        }

        $record->reply      = $reply->reply;
        $record->elementId  = $reply->elementId;
        $record->authorId   = $author->id;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $record->save(false);
            $reply->id = $record->id;

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $record;
    }

    /**
     * Delete replies by element id
     *
     * @param $element
     * @return bool|false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteRepliesByElement($element)
    {
        $record = ReplyRecord::find()
            ->where(['elementId' => $element->id])
            ->one();

        if (!$record) {
            return true;
        }

        return $record->delete();
    }

    /**
     * Delete reply by id
     *
     * @param int $replyId
     * @return bool
     * @throws \Exception
     */
    public function deleteReplyById(int $replyId): bool
    {
        return $this->delete($replyId);
    }

    /**
     * Delete
     *
     * @param int $replyId
     * @return bool
     * @throws \Exception
     */
    public function delete(int $replyId): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getDb()->createCommand()
                ->delete('{{%qarr_reviews_replies}}', ['id' => $replyId])
                ->execute();

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return true;
    }
}
