<?php

namespace owldesign\qarr\records;

use craft\db\ActiveRecord;
use owldesign\qarr\elements\db\Table;

class DirectLink extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::DIRECTLINKS;
    }
}
