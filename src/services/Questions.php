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

use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Question;
use owldesign\qarr\records\Question as QuestionRecord;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use Throwable;
use yii\db\Exception;

/**
 * Class Questions
 * @package owldesign\qarr\services
 */
class Questions extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    protected $questionRecord;
    /**
     * @var
     */
    private $_allQuestions;
    /**
     * @var
     */
    private $_questionsById;

    // Public Methods
    // =========================================================================

    /**
     * @param null $productId
     * @param null $productType
     * @return array
     */
//    public function getAllReviews($productId = null, $productType = null): array
//    {
//        if ($this->_allQuestions !== null) {
//            return $this->_allQuestions;
//        }
//
//        $this->_allQuestions = Review::findAll();
//        $this->_questionsById = ArrayHelper::index($this->_allQuestions, 'id');
//
//        return $this->_allQuestions;
//    }

    /**
     * @param $productId
     * @param int $offset
     * @return ElementQueryInterface
     */
//    public function paginateQuestionsByProductId($productId, $offset = 0)
//    {
//        $query = Question::find();
//        $query->productId($productId);
//        $query->limit(4);
//        $query->offset($offset);
//        $query->status('approved');
//
//        return $query;
//    }

    /**
     * @param $productId
     * @return ElementQueryInterface
     */
//    public function getQuestionsByProductId($productId)
//    {
//        $query = Question::find();
//        $query->productId($productId);
//        $query->status('approved');
//
//        return $query;
//    }

    /**
     * @param int $entryId
     * @return array|ElementInterface|null
     */
    public function getEntryById(int $entryId): ElementInterface|array|null
    {
        if (!$entryId) {
            return null;
        }

        $query = Question::find();
        $query->id($entryId);
        $query->anyStatus();

        return $query->one();
    }

    /**
     * @param Question $review
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function saveReview(Question $review): bool
    {
        $isNewQuestion = !$review->id;

        if ($review->id) {
            $record = QuestionRecord::findOne($review->id);
            if (!$record) {
                throw new Exception(QARR::t('No question exists with id '.$review->id));
            }
        }
        $review->validate();

        if ($review->hasErrors()) {
            QARR::error($review->getErrors());

            return false;
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $success = Craft::$app->getElements()->saveElement($review);

            if (!$success) {
                QARR::error('Couldn’t save Question Element.');
                $transaction->rollBack();

                return false;
            }

            QARR::info('Question Element Saved.');
            $transaction->commit();

        } catch (\Exception $e) {
            QARR::error('Failed to save element: '.$e->getMessage());
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param Question $question
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function deleteEntry(Question $question): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $success = Craft::$app->elements->deleteElementById($question->id);

            if (!$success) {
                $transaction->rollback();

                return false;
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }
        return true;
    }

    /**
     * @param $entries
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function deleteEntries($entries): bool
    {
        foreach ($entries as $key => $entry) {
            if ($entry) {
                $this->deleteEntry($entry);
            } else {
                QARR::error("Can't delete entry with id: {$entry->id}");
            }
        }

        return true;
    }
}
