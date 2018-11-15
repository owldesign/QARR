<?php

namespace owldesign\qarr\migrations;

use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;

use Craft;
use craft\db\Migration;

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
        $this->createTable('{{%qarr_rules}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'enabled' => $this->boolean(),
            'settings' => $this->text(),
            'options' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%qarr_rules_elements}}', [
            'id' => $this->primaryKey(),
            'ruleId' => $this->integer()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'details' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_rules_elements}}', 'ruleId'), '{{%qarr_rules_elements}}', 'ruleId', '{{%qarr_rules}}', 'id', 'CASCADE', null);

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
