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

use Craft;
use craft\base\Component;
use craft\commerce\elements\Product;

use craft\db\Query;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Display;

/**
 * Class Element
 * @package owldesign\qarr\services
 */
class Element extends Component
{
    /**
     * Query single element
     *
     * @param string $type
     * @param int $elementId
     * @return array|\craft\base\ElementInterface|null
     */
    public function getElement(string $type, int $elementId)
    {
        $query = $this->_getElementQuery($type);
        $query->id($elementId);

        return $query->one();
    }

    /**
     * @param string $type
     * @param int|null $productId
     * @param int|null $limit
     * @param int|null $offset
     * @param string $status
     * @return \craft\elements\db\ElementQueryInterface|null
     * @throws \yii\base\ExitException
     */
    public function queryElements(string $type, int $productId = null, int $limit = null, int $offset = null, string $status = 'approved')
    {
        $query = $this->_getElementQuery($type);
        $query->productId($productId);
        $query->limit($limit);
        $query->offset($offset);
        $query->status($status);

        return $query;
    }

    /**
     * Marks element as abuse
     *
     * @param int $elementId
     * @param string $type
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function reportAbuse(int $elementId, string $type)
    {
        if (!$elementId & !$type) {
            return false;
        }

        $table = '{{%qarr_'.$type.'}}';

        $result = Craft::$app->getDb()->createCommand()
            ->update($table, ['abuse' => true], ['id' => $elementId])
            ->execute();

        return $result;
    }

    /**
     * Clears elements marked with abuse
     *
     * @param int $elementId
     * @param string $type
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function clearAbuse(int $elementId, string $type)
    {
        if (!$elementId) {
            return false;
        }

        $table = '{{%qarr_'.$type.'}}';

        $result = Craft::$app->getDb()->createCommand()
            ->update($table, ['abuse' => false], ['id' => $elementId])
            ->execute();

        return $result;
    }

    /**
     * Get display element
     *
     * @param $request
     * @param $fields
     * @param $entry
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function getDisplay($request, $fields, &$entry)
    {
        $displayId = $request->getBodyParam('displayId');

        if ($displayId) {
            $display                = Display::find()->id($displayId)->anyStatus()->one();
            $entry->displayId       = $displayId;
            $entry->displayHandle   = $display->handle;

            if (isset($display->titleFormat)) {
                $fields['dateCreated'] = date('F jS, Y');
                $entry->title = Craft::$app->getView()->renderObjectTemplate($display->titleFormat, $fields);
            } else {
                $entry->title = 'Submission - '.date('F jS, Y');
            }
        } else {
            $entry->title = 'Submission - '.date('F jS, Y');
            $entry->displayId = null;
        }
    }

    /**
     * Get commerce product
     *
     * @param $request
     * @param $review
     * @return string
     */
    public function getProduct($request, &$review)
    {
        $productId = $request->getRequiredBodyParam('productId');

        if (!$productId) {
            return QARR::t('Product ID is required.');
        }

        $product = Product::find()->id($productId)->one();

        if (!$product) {
            return QARR::t('Product not found.');
        }

        $review->productId      = $productId;
        $review->productTypeId  = $product->type->id;
    }

    /**
     * Update entry status
     *
     * @param int $elementId
     * @param string $status
     * @param string $type
     * @return int|null
     * @throws \yii\db\Exception
     */
    public function updateStatus(int $elementId, string $status, string $type)
    {
        if (!$elementId) {
            return null;
        }

        $table = '{{%qarr_'.$type.'}}';

        $result = Craft::$app->getDb()->createCommand()
            ->update($table, ['status' => $status], ['id' => $elementId])
            ->execute();

        return $result;
    }

    /**
     * Update all entry statuses
     *
     * @param $entries
     * @param $status
     * @param $type
     * @return bool
     * @throws \yii\db\Exception
     */
    public function updateAllStatuses($entries, $status, $type)
    {
        foreach ($entries as $key => $entry) {
            if ($entry) {
                $this->updateStatus($entry->id, $status, $type);
            } else {
                QARR::error("Can't update status");
            }
        }

        return true;
    }

    /**
     * Get entry count
     *
     * @param $type
     * @param $status
     * @param $productId
     * @param $productTypeId
     * @return int|null
     */
    public function getCount($type, $status, $productId = null, $productTypeId = null)
    {
        $query = $this->_getElementQuery($type);

        if (!$query) {
            return null;
        }

        if ($productTypeId) {
            $query->productTypeId($productTypeId);
        }

        if ($productId) {
            $query->productId($productId);
        }

        $query->status($status);

        return $query->count();
    }

    public function getAverageRating($productId)
    {
        $query = new Query();
        $query->select('rating')
            ->from('{{%qarr_reviews}}')
            ->where(['status' => 'approved', 'productId' => $productId]);

        $count = $query->count();
        $sum = $query->sum('rating');

        if (!$sum) {
            return 0;
        }

        $average = $sum / $count;

        return $average;
    }

    // Private Methods
    // =========================================================================


    /**
     * Return element query
     *
     * @param $type
     * @return \craft\elements\db\ElementQueryInterface|null
     */
    private function _getElementQuery($type)
    {
        if ($type === 'reviews') {
            $query = Review::find();
        } elseif ($type === 'questions') {
            $query = Question::find();
        } else {
            return null;
        }

        return $query;
    }
}