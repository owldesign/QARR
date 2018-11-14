<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;

class Rule extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_rules}}';
    }
}
