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
use craft\base\ElementInterface;
use craft\commerce\elements\Product;

use craft\db\Query;

use owldesign\qarr\QARR;
use owldesign\qarr\plugin\Table;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Display;
use craft\elements\db\ElementQueryInterface;
use Throwable;
use yii\data\Pagination;
use yii\db\Exception;

//require_once CRAFT_VENDOR_PATH . '/owldesign/qarr/src/functions/array-group-by.php';

/**
 * Class Element
 * @package owldesign\qarr\services
 */
class Elements extends Component
{
    /**
     * Query single element
     *
     * @param string $type
     * @param int $elementId
     * @return array|ElementInterface|null
     */
    public function getElement(string $type, int $elementId): ElementInterface|array|null
    {
        $query = $this->_getElementQuery($type);
        $query->id($elementId);

        return $query->one();
    }

    /**
     * @param string $type
     * @param string $order
     * @param int|null $elementId
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $status
     * @param array $exclude
     * @return ElementQueryInterface|null
     */
    public function queryElements(string $type, string $order = 'dateCreated desc', int $elementId = null, int $limit = null, int $offset = null, string $status = null, array $exclude = []): ?ElementQueryInterface
    {
        $query = $type::find();
        $query->elementId($elementId);
        $query->limit($limit);

        if ($exclude) {
            $query->id('and, not ' . implode(', not ', $exclude));
        }

        $query->offset($offset);
        $query->status($status);
        $query->orderBy($order);

        return $query;
    }

    /**
     * Query sort elements
     *
     * @param string $type
     * @param string $order
     * @param $elementId
     * @param $limit
     * @return ElementQueryInterface|null
     */
    public function querySortElements(string $type, string $order, $elementId, $limit): ?ElementQueryInterface
    {
        $query = $type::find();

        if ($elementId) {
            $query->elementId($elementId);
        }

        $query->limit($limit);
        $query->orderBy($order);
        $query->status('approved');

        return $query;
    }

    /**
     * Query star filtered elements
     *
     * @param string $type
     * @param int $elementId
     * @param $rating
     * @param string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return ElementQueryInterface|null
     */
    public function queryStarFilteredElements(string $type, int $elementId, $rating, string $order, int $limit = null, int $offset = null): ?ElementQueryInterface
    {
        $query = $this->_getElementQuery($type);
        $query->elementId($elementId);

        if ($rating) {
            $query->rating($rating);
        }

        $query->limit($limit);
        $query->orderBy($order);
        $query->offset($offset);

        $query->status('approved');

        return $query;
    }

    /**
     * Marks element as abuse
     *
     * @param int $elementId
     * @param string $type
     * @return bool|int
     * @throws Exception
     */
    public function reportAbuse(int $elementId, string $type): bool|int
    {
        if (!$elementId & !$type) {
            return false;
        }

        $table = '{{%qarr_' . $type . '}}';

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
     * @throws Exception
     */
    public function clearAbuse(int $elementId, string $type): bool|int
    {
        if (!$elementId) {
            return false;
        }

        $table = '{{%qarr_' . $type . '}}';

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
     * @throws Throwable
     * @throws \yii\base\Exception
     */
    public function getDisplay($request, $fields, &$entry): void
    {
        $displayHandle = $request->getBodyParam('displayHandle');

        if ($displayHandle) {
            $display = Display::find()->handle($displayHandle)->anyStatus()->one();
            $entry->displayId = $display->id;
            $entry->displayHandle = $display->handle;

            if (isset($display->titleFormat) && $display->titleFormat != '') {
                $fields['dateCreated'] = date('F jS, Y');
                $entry->title = Craft::$app->getView()->renderObjectTemplate($display->titleFormat, $fields);
            } else {
                $entry->title = 'Submission - ' . date('F jS, Y');
            }
        } else {
            $entry->title = 'Submission - ' . date('F jS, Y');
            $entry->displayId = null;
        }
    }

    /**
     * Set element data
     *
     * @param $request
     * @param $review
     * @return string
     */
    public function setElementData($request, $review): string
    {
        $elementId = $request->getRequiredBodyParam('elementId');

        if (!$elementId) {
            return QARR::t('Element ID is required.');
        }

        $element = Craft::$app->elements->getElementById($elementId);

        if (!$element) {
            return QARR::t('Element not found.');
        }

        // Check Element Type
        if (property_exists($element, 'typeId')) {
            if ($element->type->elementType === 'craft\\elements\\Entry') {
                $review->sectionId = $element->typeId;
            } else if ($element->type->elementType === 'craft\\commerce\\elements\\Product') {
                $review->productTypeId = $element->typeId;
            }
        }

        $review->elementId = $elementId;

        return $review;
    }

    /**
     * Update entry status
     *
     * @param int $elementId
     * @param string $status
     * @param string $type
     * @return int|null
     * @throws Exception
     */
    public function updateStatus(int $elementId, string $status, string $type): ?int
    {
        if (!$elementId) {
            return null;
        }

        if ($type === 'owldesign\\qarr\\elements\\Question') {
            $type = 'questions';
        } else {
            $type = 'reviews';
        }

        $table = '{{%qarr_' . $type . '}}';

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
     * @throws Exception
     */
    public function updateAllStatuses($entries, $status, $type): bool
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
     * @param $elementId
     * @param $elementType
     * @param $elementTypeId
     * @return int|null
     */
    public function getCount($type, $status, $elementId = null, $elementType = null, $elementTypeId = null): ?int
    {
        $query = $this->_getElementQuery($type);

        if (!$query) {
            return null;
        }

        if ($elementType != '*') {
            if ($elementType && $elementTypeId) {
                $query->{$elementType}($elementTypeId);
            }
        }

        if ($elementId) {
            $query->elementId($elementId);
        }

        $query->status($status);

        return $query->count();
    }

    /**
     * Get total count of approved elements
     *
     * @return int
     */
    public function getTotalApproved(): int
    {
        $reviews = Review::find()->where(['status' => 'approved'])->count();
        $questions = Question::find()->where(['status' => 'approved'])->count();
        $total = $reviews + $questions;

        return $total;
    }

    /**
     * Get total count of pending elements
     *
     * @return int
     */
    public function getTotalPending(): int
    {
        $reviews = Review::find()->where(['status' => 'pending'])->count();
        $questions = Question::find()->where(['status' => 'pending'])->count();
        $total = $reviews + $questions;

        return $total;
    }

    /**
     * Get entries by rating
     *
     * @param $status
     * @param $elementId
     * @return array
     */
    public function getEntriesByRating($status, $elementId): array
    {
        $query = Review::find();
        $query->status($status);
        $query->elementId($elementId);

        $grouped = QARR::$app->functions->groupBy($query->all(), 'rating');

        $newGroup = [];
        for ($i = 1; $i <= 5; $i++) {
            $newGroup[$i] = [
                'entries' => isset($grouped[$i]) ? $grouped[$i] : null,
                'total' => isset($grouped[$i]) ? count($grouped[$i]) : 0
            ];
        }
        ksort($newGroup, SORT_NUMERIC);

        return $newGroup;
    }

    /**
     * Average Count
     *
     * @param $elementId
     * @return float|int
     */
    public function getAverageRating($elementId): float|int
    {
        $query = new Query();
        $query->select('rating')
            ->from(Table::REVIEWS)
            ->where(['status' => 'approved', 'elementId' => $elementId, 'dateDeleted' => null]);

        $count = $query->count();
        $sum = $query->sum('rating');

        if (!$sum) {
            return 0;
        }

        return $sum / $count;
    }

    /**
     * Return allowed element types
     *
     * @return array
     */
    public function allowedElementTypes(): array
    {
        $elements = Craft::$app->getElements()->getAllElementTypes();
        $allowedElements = $this->_allowedElements();
        $list = [];

        foreach ($elements as $element) {
            if (in_array($element, $allowedElements)) {
                $list[] = $element;
            }
        }

        return $list;
    }

    /**
     * Get element type by name
     *
     * @param $type
     * @return string
     */
    public function getElementTypeByName($type): string
    {
        if ($type == 'product') {
            return 'craft\\commerce\\elements\\Product';
        }

        return 'craft\\elements\\Entry';
    }

    public function markElementsAsDeletedByElementId($elementId, $date): true
    {
        $reviews = Craft::$app->getDb()->createCommand()
            ->update('{{%qarr_reviews}}',
                ['dateDeleted' => $date->format('Y-m-d H:i:s')],
                ['elementId' => $elementId])
            ->execute();

        $questions = Craft::$app->getDb()->createCommand()
            ->update('{{%qarr_questions}}',
                ['dateDeleted' => $date->format('Y-m-d H:i:s')],
                ['elementId' => $elementId])
            ->execute();

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Set allowed element types
     *
     * @return array
     */
    private function _allowedElements(): array
    {
        return [
            'craft\\elements\\Entry',
            'craft\\commerce\\elements\\Product'
        ];
    }

    /**
     * Return element query
     *
     * @param $type
     * @return ElementQueryInterface|null
     */
    private function _getElementQuery($type): ?ElementQueryInterface
    {
        if ($type === 'reviews') {
            return Review::find();
        } else {
            return Question::find();
        }
    }
}
