<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;

use owldesign\qarr\elements\db\Table;

/**
 * m190614_071148_UpdateProductTypeColumn migration.
 */
class m190614_071148_UpdateProductTypeColumn extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Drop foreign key
        $this->dropForeignKey($this->db->getForeignKeyName(Table::REVIEWS, 'productTypeId'), Table::REVIEWS);
        $this->dropForeignKey($this->db->getForeignKeyName(Table::QUESTIONS, 'productTypeId'), Table::QUESTIONS);

        $this->alterColumn(Table::REVIEWS, 'productTypeId', $this->integer());
        $this->alterColumn(Table::QUESTIONS, 'productTypeId', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190614_071148_UpdateProductTypeColumn cannot be reverted.\n";
        return false;
    }
}
