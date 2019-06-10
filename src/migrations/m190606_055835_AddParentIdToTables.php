<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;

use owldesign\qarr\elements\db\Table;

/**
 * m190606_055835_AddParentIdToTables migration.
 */
class m190606_055835_AddParentIdToTables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::REVIEWS, 'parentId', $this->integer()->notNull());
        $this->addColumn(Table::QUESTIONS, 'parentId', $this->integer()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
