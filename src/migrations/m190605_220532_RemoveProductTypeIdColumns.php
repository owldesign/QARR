<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;

use owldesign\qarr\elements\db\Table;

/**
 * m190605_220532_RemoveProductTypeIdColumns migration.
 */
class m190605_220532_RemoveProductTypeIdColumns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Drop foreign key
        $this->dropForeignKey($this->db->getForeignKeyName(Table::REVIEWS, 'productTypeId'), Table::REVIEWS);
        $this->dropForeignKey($this->db->getForeignKeyName(Table::QUESTIONS, 'productTypeId'), Table::QUESTIONS);

        // Drop Indexes
        $this->dropIndex($this->db->getIndexName(Table::REVIEWS, 'productTypeId', false), Table::REVIEWS);
        $this->dropIndex($this->db->getIndexName(Table::QUESTIONS, 'productTypeId', false), Table::QUESTIONS);

        // Drop Columns
        $this->dropColumn(Table::REVIEWS, 'productTypeId');
        $this->dropColumn(Table::QUESTIONS, 'productTypeId');

        // Drop hasPurchased column
        $this->dropColumn(Table::REVIEWS, 'hasPurchased');
        $this->dropColumn(Table::QUESTIONS, 'hasPurchased');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
