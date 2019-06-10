<?php

namespace owldesign\qarr\migrations;


use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;

use owldesign\qarr\elements\db\Table;

/**
 * m190605_182314_UpdateReviewsAndQuestionsTableToElementColumns migration.
 */
class m190605_182314_UpdateReviewsAndQuestionsTableToElementColumns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Drop foreign key
        $this->dropForeignKey($this->db->getForeignKeyName(Table::REVIEWS, 'productId'), Table::REVIEWS);
        $this->dropForeignKey($this->db->getForeignKeyName(Table::QUESTIONS, 'productId'), Table::QUESTIONS);

        // Drop Indexes
        $this->dropIndex($this->db->getIndexName(Table::REVIEWS, 'productId', false), Table::REVIEWS);
        $this->dropIndex($this->db->getIndexName(Table::QUESTIONS, 'productId', false), Table::QUESTIONS);

        // Rename columns
        $this->renameColumn(Table::REVIEWS, 'productId', 'elementId');
        $this->renameColumn(Table::QUESTIONS, 'productId', 'elementId');

        // Create new Index
        $this->createIndex($this->db->getIndexName(Table::REVIEWS, 'elementId', false), Table::REVIEWS, 'elementId', false);
        $this->createIndex($this->db->getIndexName(Table::QUESTIONS, 'elementId', false), Table::QUESTIONS, 'elementId', false);

        // Create new foreign key
        $this->addForeignKey($this->db->getForeignKeyName(Table::REVIEWS, 'elementId'), Table::REVIEWS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONS, 'elementId'), Table::QUESTIONS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
