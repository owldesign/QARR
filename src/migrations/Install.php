<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\migrations;

use owldesign\qarr\QARR;
use owldesign\qarr\models\Rule;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

class Install extends Migration
{
    // Public Properties
    // =========================================================================

    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        // Refresh the db schema caches
        Craft::$app->db->schema->refresh();

        $this->insertDefaultData();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Create tables
     */
    protected function createTables()
    {
        // Reviews
        $this->createTable('{{%qarr_reviews}}', [
            'id' => $this->primaryKey(),
            'fullName' => $this->string()->notNull(),
            'emailAddress' => $this->string()->notNull(),
            'feedback' => $this->text()->notNull(),
            'rating' => $this->enum('rating', ['1', '2', '3', '4', '5'])->notNull()->defaultValue('1'),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'options' => $this->text(),

            'hasPurchased' => $this->boolean()->notNull()->defaultValue(false),
            'isNew' => $this->boolean()->notNull()->defaultValue(true),
            'abuse' => $this->boolean()->notNull()->defaultValue(false),
            'votes' => $this->integer(),

            'displayId' => $this->integer(),
            'productId' => $this->integer()->notNull(),
            'productTypeId' => $this->integer()->notNull(),

            'ipAddress' => $this->string()->notNull(),
            'userAgent' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%qarr_reviews_replies}}', [
            'id' => $this->primaryKey(),
            'reply' => $this->text(),
            'elementId' => $this->integer()->notNull(),
            'authorId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        // Questions
        $this->createTable('{{%qarr_questions}}', [
            'id' => $this->primaryKey(),
            'fullName' => $this->string()->notNull(),
            'emailAddress' => $this->string()->notNull(),
            'question' => $this->text()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'options' => $this->text(),

            'hasPurchased' => $this->boolean()->notNull()->defaultValue(false),
            'isNew' => $this->boolean()->notNull()->defaultValue(true),
            'abuse' => $this->boolean()->notNull()->defaultValue(false),
            'votes' => $this->integer(),

            'displayId' => $this->integer(),
            'productId' => $this->integer()->notNull(),
            'productTypeId' => $this->integer()->notNull(),

            'ipAddress' => $this->string()->notNull(),
            'userAgent' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%qarr_questions_answers}}', [
            'id' => $this->primaryKey(),
            'answer' => $this->text(),
            'elementId' => $this->integer()->notNull(),
            'anonymous' => $this->boolean(),
            'authorId' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'abuse' => $this->boolean(),
            'isHelpful' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%qarr_questions_answers_comments}}', [
            'id' => $this->primaryKey(),
            'answerId' => $this->integer()->notNull(),
            'comment' => $this->text(),
            'authorId' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'abuse' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        // Displays
        $this->createTable('{{%qarr_displays}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'fieldLayoutId' => $this->integer(),
            'titleFormat' => $this->string(),
            'enabled' => $this->boolean()->defaultValue(true),
            'options' => $this->mediumText(),
            'settings' => $this->mediumText(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%qarr_correspondence}}', [
            'id' => $this->primaryKey(),
            'email' => $this->text(),
            'response' => $this->text(),
            'allowReplies' => $this->boolean()->defaultValue(false),
            'password' => $this->string()->notNull(),
            'ownerEmail' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%qarr_correspondence_responses}}', [
            'id' => $this->primaryKey(),

            'response' => $this->text()->notNull(),
            'parentId' => $this->integer()->notNull(),
            'helpfulResponse' => $this->integer(),
            'isNew' => $this->boolean()->defaultValue(true),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%qarr_notes}}', [
            'id' => $this->primaryKey(),
            'note' => $this->text(),
            'elementId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'authorId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        // Rules
        $this->createTable('{{%qarr_rules}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
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
    }

    /**
     * Create indexes
     */
    protected function createIndexes()
    {
        $this->createIndex($this->db->getIndexName('{{%qarr_reviews}}', 'emailAddress', false), '{{%qarr_reviews}}', 'emailAddress', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_reviews}}', 'status', false), '{{%qarr_reviews}}', 'status', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_reviews}}', 'displayId', false), '{{%qarr_reviews}}', 'displayId', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_reviews}}', 'productId', false), '{{%qarr_reviews}}', 'productId', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_reviews}}', 'productTypeId', false), '{{%qarr_reviews}}', 'productTypeId', false);

        $this->createIndex($this->db->getIndexName('{{%qarr_questions}}', 'emailAddress', false), '{{%qarr_questions}}', 'emailAddress', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_questions}}', 'status', false), '{{%qarr_questions}}', 'status', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_questions}}', 'displayId', false), '{{%qarr_questions}}', 'displayId', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_questions}}', 'productId', false), '{{%qarr_questions}}', 'productId', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_questions}}', 'productTypeId', false), '{{%qarr_questions}}', 'productTypeId', false);

        $this->createIndex($this->db->getIndexName('{{%qarr_displays}}', 'name', false), '{{%qarr_displays}}', 'name', false);
        $this->createIndex($this->db->getIndexName('{{%qarr_displays}}', 'handle', false), '{{%qarr_displays}}', 'handle', false);

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Add foreign keys
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_reviews}}', 'productId'), '{{%qarr_reviews}}', 'productId', '{{%commerce_products}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_reviews}}', 'productTypeId'), '{{%qarr_reviews}}', 'productTypeId', '{{%commerce_producttypes}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_reviews_replies}}', 'elementId'), '{{%qarr_reviews_replies}}', 'elementId', '{{%qarr_reviews}}', 'id', 'CASCADE', null);

        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_reviews_replies}}', 'authorId'), '{{%qarr_reviews_replies}}', 'authorId', '{{%users}}', 'id', 'CASCADE', null);

        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions}}', 'productId'), '{{%qarr_questions}}', 'productId', '{{%commerce_products}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions}}', 'productTypeId'), '{{%qarr_questions}}', 'productTypeId', '{{%commerce_producttypes}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions_answers}}', 'elementId'), '{{%qarr_questions_answers}}', 'elementId', '{{%qarr_questions}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions_answers}}', 'authorId'), '{{%qarr_questions_answers}}', 'authorId', '{{%users}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions_answers_comments}}', 'answerId'), '{{%qarr_questions_answers_comments}}', 'answerId', '{{%qarr_questions_answers}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_questions_answers_comments}}', 'authorId'), '{{%qarr_questions_answers_comments}}', 'authorId', '{{%users}}', 'id', 'CASCADE', null);

        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_displays}}', 'fieldLayoutId'), '{{%qarr_displays}}', 'fieldLayoutId', '{{%fieldlayouts}}', 'id', 'SET NULL', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_notes}}', 'elementId'), '{{%qarr_notes}}', 'elementId', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_correspondence_responses}}', 'parentId'), '{{%qarr_correspondence_responses}}', 'parentId', '{{%qarr_correspondence}}', 'id', 'CASCADE', null);

        $this->addForeignKey($this->db->getForeignKeyName('{{%qarr_rules_elements}}', 'ruleId'), '{{%qarr_rules_elements}}', 'ruleId', '{{%qarr_rules}}', 'id', 'CASCADE', null);
    }

    /**
     * Remove tables
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%qarr_questions_answers_comments}}');
        $this->dropTableIfExists('{{%qarr_questions_answers}}');
        $this->dropTableIfExists('{{%qarr_reviews_replies}}');
        $this->dropTableIfExists('{{%qarr_reviews}}');
        $this->dropTableIfExists('{{%qarr_questions}}');
        $this->dropTableIfExists('{{%qarr_displays}}');
        $this->dropTableIfExists('{{%qarr_replies}}');
        $this->dropTableIfExists('{{%qarr_notes}}');
        $this->dropTableIfExists('{{%qarr_correspondence_responses}}');
        $this->dropTableIfExists('{{%qarr_correspondence}}');
        $this->dropTableIfExists('{{%qarr_rules_elements}}');
        $this->dropTableIfExists('{{%qarr_rules}}');
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
