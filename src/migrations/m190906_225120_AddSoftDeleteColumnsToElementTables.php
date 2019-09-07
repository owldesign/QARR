<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;

/**
 * m190906_225120_AddSoftDeleteColumnsToElementTables migration.
 */
class m190906_225120_AddSoftDeleteColumnsToElementTables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%qarr_reviews}}', 'dateDeleted', $this->dateTime()->null()->after('dateUpdated'));
        $this->addColumn('{{%qarr_questions}}', 'dateDeleted', $this->dateTime()->null()->after('dateUpdated'));
        $this->addColumn('{{%qarr_displays}}', 'dateDeleted', $this->dateTime()->null()->after('dateUpdated'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190906_225120_AddSoftDeleteColumnsToElementTables cannot be reverted.\n";
        return false;
    }
}
