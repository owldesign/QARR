<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use owldesign\qarr\elements\db\Table;

/**
 * m190907_231937_AddEmailTemplatesTable migration.
 */
class m190907_231937_AddEmailTemplatesTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::EMAIL_TEMPLATES, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'templatePath' => $this->string()->null(),
            'enabled' => $this->boolean(),
            'bodyHtml' => $this->text(),
            'bodyRaw' => $this->text(),
            'footerHtml' => $this->text(),
            'footerRaw' => $this->text(),
            'settings' => $this->text(),
            'options' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid()
        ]);

        // Add Email Template ID column to Correspondence table
        $this->addColumn(Table::CORRESPONDENCE, 'emailTemplateId', $this->integer()->null()->after('elementId'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Table::EMAIL_TEMPLATES);

        return false;
    }
}
