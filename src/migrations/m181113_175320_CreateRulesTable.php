<?php

namespace owldesign\qarr\migrations;


use Craft;
use craft\db\Migration;

use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\db\Table;

/**
 * m181113_175320_CreateRulesTable migration.
 */
class m181113_175320_CreateRulesTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::RULES, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'enabled' => $this->boolean(),
            'data' => $this->longText(),
            'icon' => $this->string()->defaultValue('exclamation'),
            'settings' => $this->text(),
            'options' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(Table::RULESFLAGGED, [
            'id' => $this->primaryKey(),
            'ruleId' => $this->integer()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'details' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey($this->db->getForeignKeyName(Table::RULESFLAGGED, 'ruleId'), Table::RULESFLAGGED, 'ruleId', Table::RULES, 'id', 'CASCADE', null);

        $this->insertDefaultData();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181113_175320_CreateRulesTable cannot be reverted.\n";
        return false;
    }

    public function insertDefaultData()
    {
        $profanityRule = new Rule([
            'name' => 'Profanity',
            'handle' => 'profanity'
        ]);

        $negativeFeedbackRule = new Rule([
            'name' => 'Negative Feedback',
            'handle' => 'negativeFeedback'
        ]);

        QARR::$plugin->rules->saveRule($profanityRule);
        QARR::$plugin->rules->saveRule($negativeFeedbackRule);
    }
}
