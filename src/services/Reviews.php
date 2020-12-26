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
use owldesign\qarr\elements\Review;
use owldesign\qarr\records\Review as ReviewRecord;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;

/**
 * Class Reviews
 * @package owldesign\qarr\services
 */
class Reviews extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    protected $reviewRecord;
    /**
     * @var
     */
    private $_allReviews;
    /**
     * @var
     */
    private $_reviewsById;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function getAllReviews(): array
    {
        if ($this->_allReviews !== null) {
            return $this->_allReviews;
        }

        $this->_allReviews = Review::findAll();
        $this->_reviewsById = ArrayHelper::index($this->_allReviews, 'id');

        return $this->_allReviews;
    }

    /**
     * @param $productId
     * @return \craft\elements\db\ElementQueryInterface
     */
//    public function getReviewsByProductId($productId)
//    {
//        $query = Review::find();
//        $query->productId($productId);
//        $query->status('approved');
//
//        return $query;
//    }

    /**
     * @param int $entryId
     * @return array|\craft\base\ElementInterface|null
     */
    public function getEntryById(int $entryId)
    {
        if (!$entryId) {
            return null;
        }

        $query = Review::find();
        $query->id($entryId);
        $query->anyStatus();

        return $query->one();
    }

    /**
     * @param Review $review
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveReview(Review $review)
    {
        $isNewReview = !$review->id;

        if ($review->id) {
            $record = ReviewRecord::findOne($review->id);

            if (!$record) {
                throw new Exception(QARR::t('No review exists with id '.$review->id));
            }
        }
        $review->validate();

        if ($review->hasErrors()) {
            QARR::error($review->getErrors());

            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $success = Craft::$app->getElements()->saveElement($review);

            if (!$success) {
                QARR::error('Couldn’t save Review Element.');
                $transaction->rollBack();

                return false;
            }

            QARR::info('Review Element Saved.');
            $transaction->commit();

        } catch (\Exception $e) {
            QARR::error('Failed to save element: '.$e->getMessage());
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param Review $review
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function deleteEntry(Review $review)
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $success = Craft::$app->elements->deleteElementById($review->id);

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
     * Delete entries
     *
     * @param $entries
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function deleteEntries($entries)
    {
        foreach ($entries as $key => $entry) {

            $entry = $this->getEntryById($entry->id);

            if ($entry) {
                $this->deleteEntry($entry);
            } else {
                QARR::error("Can't delete entry with id: {$entry->id}");
            }
        }

        return true;
    }
}
