<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;

class Flagged extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_rules_elements}}';
    }
}
