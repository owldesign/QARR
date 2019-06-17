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

use Craft;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use owldesign\qarr\elements\Question;
use owldesign\qarr\QARR;

class QuestionQuery extends ElementQuery
{
    // Public Properties
    // =========================================================================

    public $fullName;
    public $emailAddress;
    public $question;
    public $status;
    public $options;
    public $hasPurchased;
    public $isNew;
    public $abuse;
    public $votes;

    public $elementId;
    public $sectionId;
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
     * Return Section ID
     *
     * @param $value
     * @return $this
     */
    public function sectionId($value)
    {
        $this->sectionId = $value;
        return $this;
    }

    /**
     * Return Product Type ID
     *
     * @param $value
     * @return $this
     */
    public function productTypeId($value)
    {
        $this->productTypeId = $value;
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

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('qarr_questions');

        $this->query->select([
            'qarr_questions.fullName',
            'qarr_questions.emailAddress',
            'qarr_questions.question',
            'qarr_questions.status',
            'qarr_questions.options',
            'qarr_questions.hasPurchased',
            'qarr_questions.elementId',
            'qarr_questions.sectionId',
            'qarr_questions.productTypeId',
            'qarr_questions.geolocation',
            'qarr_questions.ipAddress',
            'qarr_questions.userAgent',
            'qarr_questions.dateCreated',
            'qarr_questions.dateUpdated'
        ]);

        if ($this->fullName) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.fullName', $this->fullName));
        }

        if ($this->emailAddress) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.emailAddress', $this->emailAddress));
        }

        if ($this->question) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.question', $this->question));
        }

        if ($this->status) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.status', $this->status));
        }

        if ($this->options) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.options', $this->options));
        }

        if ($this->elementId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.elementId', $this->elementId));
        }

        if ($this->sectionId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.sectionId', $this->sectionId));
        }

        if ($this->productTypeId) {
            $this->subQuery->andWhere(Db::parseParam('qarr_questions.productTypeId', $this->productTypeId));
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        $statuses = Question::statuses();

        foreach ($statuses as $key => $value) {

            if ($key == $status) {
                return ['qarr_questions.status' => $status];
            }
        };
    }

}