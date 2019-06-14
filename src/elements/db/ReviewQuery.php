<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use craft\helpers\Json;
use owldesign\qarr\elements\Review;

class ReviewQuery extends ElementQuery
{
    // Public Properties
    // =========================================================================
    public $fullName;
    public $emailAddress;
    public $rating;
    public $feedback;
    public $status;
    public $options;
    public $isNew;
    public $abuse;
    public $votes;
    public $displayId;
    public $elementId;
    public $element;
    public $sectionId;
    public $structureId;
    public $productTypeId;
    public $geolocation;
    public $ipAddress;
    public $userAgent;

    // Public Methods
    // =========================================================================
    /**
     * Return Element ID
     *
     * @param $value
     * @return $this
     */
    public function elementId($value)
    {
        $this->elementId = $value;
        return $this;
    }

    /**
     * Return Status
     *
     * @param $value
     * @return $this
     */
    public function status($value)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * Rating
     *
     * @param $value
     * @return $this
     */
    public function rating($value)
    {
        $this->rating = $value;
        return $this;
    }

    public function geolocation($value)
    {
        $this->geolocation = Json::decode($value);
        return $this;
    }
    // Protected Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('qarr_reviews');
        $this->query->select([
            'qarr_reviews.fullName',
            'qarr_reviews.emailAddress',
            'qarr_reviews.rating',
            'qarr_reviews.feedback',
            'qarr_reviews.status',
            'qarr_reviews.options',
            'qarr_reviews.isNew',
            'qarr_reviews.votes',
            'qarr_reviews.abuse',
            'qarr_reviews.displayId',
            'qarr_reviews.elementId',
            'qarr_reviews.sectionId',
            'qarr_reviews.structureId',
            'qarr_reviews.productTypeId',
            'qarr_reviews.geolocation',
            'qarr_reviews.ipAddress',
            'qarr_reviews.userAgent',
            'qarr_reviews.dateCreated',
            'qarr_reviews.dateUpdated'
        ]);
        if ($this->fullName) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.fullName', $this->fullName));
        }
        if ($this->emailAddress) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.emailAddress', $this->emailAddress));
        }
        if ($this->rating) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.rating', $this->rating));
        }
        if ($this->feedback) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.feedback', $this->feedback));
        }
        if ($this->status) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.status', $this->status));
        }
        if ($this->options) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.options', $this->options));
        }
        if ($this->displayId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.displayId', $this->displayId));
        }
        if ($this->elementId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.elementId', $this->elementId));
        }
        if ($this->sectionId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.sectionId', $this->sectionId));
        }
        if ($this->structureId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.structureId', $this->structureId));
        }
        if ($this->productTypeId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.productTypeId', $this->productTypeId));
        }
        if ($this->abuse) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.abuse', $this->abuse));
        }
        if ($this->votes) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.votes', $this->votes));
        }
        if ($this->isNew) {
            $this->subQuery->andWhere(Db::parseParam('qarr_reviews.isNew', $this->isNew));
        }
        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        $statuses = Review::statuses();
        foreach ($statuses as $key => $value) {
            if ($key == $status) {
                return ['qarr_reviews.status' => $status];
            }
        };
    }
}