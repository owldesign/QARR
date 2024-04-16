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

class DisplayQuery extends ElementQuery
{
    // Public Properties
    // =========================================================================

    public mixed $id;
    public string $name;
    public string $handle;
    public int $fieldLayoutId;
    public string $titleFormat;
    public $options;
    public int $totalSubmissions;
    public string $customTemplates;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'qarr_displays.name';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[name]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function name($value): static
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Sets the [[handle]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function handle($value): static
    {
        $this->handle = $value;
        return $this;
    }

    public function id($value): ElementQuery
    {
        $this->id = $value;
        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('qarr_displays');

        $this->query->select([
            'qarr_displays.id',
            'qarr_displays.name',
            'qarr_displays.handle',
            'qarr_displays.fieldLayoutId',
            'qarr_displays.titleFormat',
            'qarr_displays.dateCreated',
            'qarr_displays.dateUpdated'
        ]);

        if ($this->totalSubmissions) {
            $this->query->addSelect('COUNT(entries.id) totalSubmissions');
            $this->query->leftJoin('qarr_reviews entries', 'entries.displayId = qarr_reviews.id');
        }

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam('qarr_displays.id', $this->id));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('qarr_displays.handle', $this->handle));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('qarr_displays.name', $this->name));
        }

        if ($this->titleFormat) {
            $this->subQuery->andWhere(Db::parseParam('qarr_displays.titleFormat', $this->titleFormat));
        }

        return parent::beforePrepare();
    }
}