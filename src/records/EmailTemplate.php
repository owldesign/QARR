<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;
use owldesign\qarr\elements\db\Table;

class EmailTemplate extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::EMAIL_TEMPLATES;
    }
}
