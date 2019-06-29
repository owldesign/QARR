<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use owldesign\qarr\elements\db\Table;

/**
 * m190628_180541_AddColumnsToCorrespondenceTable migration.
 */
class m190628_180541_AddColumnsToCorrespondenceTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::CORRESPONDENCE, 'subject', $this->string());
        $this->addColumn(Table::CORRESPONDENCE, 'opened', $this->boolean()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190628_180541_AddColumnsToCorrespondenceTable cannot be reverted.\n";
        return false;
    }
}
