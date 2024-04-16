<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\records;

use craft\db\SoftDeleteTrait;
use craft\db\ActiveRecord;
use craft\records\FieldLayout;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property-read mixed $oldFieldLayoutId
 * @property-read ActiveQueryInterface $fieldLayout
 * @property-read mixed $oldHandle
 */
class Display extends ActiveRecord
{
    use SoftDeleteTrait;

    private $_oldHandle;
    private $_oldFieldLayoutId;

    // Public Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%qarr_displays}}';
    }

    /**
     * Returns the fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFieldLayout(): ActiveQueryInterface
    {
        return self::hasOne(FieldLayout::class,
            ['id' => 'fieldLayoutId']);
    }

    function afterFind(): void
    {
        $this->_oldHandle = $this->handle;
        $this->_oldFieldLayoutId = $this->fieldLayoutId;
    }

    public function getOldHandle()
    {
        return $this->_oldHandle;
    }

    public function getOldFieldLayoutId()
    {
        return $this->_oldFieldLayoutId;
    }
}
