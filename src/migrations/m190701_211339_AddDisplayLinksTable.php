<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;
use owldesign\qarr\elements\db\Table;

/**
 * m190701_211339_AddDisplayLinksTable migration.
 */
class m190701_211339_AddDisplayLinksTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::DIRECTLINKS, [
            'id' => $this->primaryKey(),
            'slug' => $this->string()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'enabled' => $this->boolean(),
            'completed' => $this->boolean()->defaultValue(false),
            'settings' => $this->text(),
            'options' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'elementId'), Table::DIRECTLINKS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'userId'), Table::DIRECTLINKS, 'userId', CraftTable::USERS, 'id', 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Table::DIRECTLINKS);


        // Drop foreign key
        $this->dropForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'elementId'), Table::DIRECTLINKS);
        $this->dropForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'userId'), Table::DIRECTLINKS);

        return false;
    }
}
