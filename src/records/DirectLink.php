<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;

class DirectLink extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qarr_direct_links}}';
    }
}
