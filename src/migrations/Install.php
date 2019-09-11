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
use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\db\Table as CraftTable;
use owldesign\qarr\QARR;
use owldesign\qarr\models\Rule;
use owldesign\qarr\elements\db\Table;
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
        $this->createTable(Table::REVIEWS, [
            'id' => $this->primaryKey(),
            'fullName' => $this->string()->notNull(),
            'emailAddress' => $this->string()->notNull(),
            'feedback' => $this->text()->notNull(),
            'rating' => $this->enum('rating', ['1', '2', '3', '4', '5'])->notNull()->defaultValue('1'),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'options' => $this->text(),
            'isNew' => $this->boolean()->notNull()->defaultValue(true),
            'abuse' => $this->boolean()->notNull()->defaultValue(false),
            'hasPurchased' => $this->boolean()->defaultValue(false),
            'votes' => $this->integer(),
            'displayId' => $this->integer(),
            'elementId' => $this->integer()->notNull(),
            'sectionId' => $this->integer(),
            'structureId' => $this->integer(),
            'productTypeId' => $this->integer(),
            'geolocation' => $this->text(),
            'ipAddress' => $this->string()->notNull(),
            'userAgent' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
        $this->createTable(Table::REVIEWSREPLIES, [
            'id' => $this->primaryKey(),
            'reply' => $this->text(),
            'elementId' => $this->integer()->notNull(),
            'authorId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        // Questions
        $this->createTable(Table::QUESTIONS, [
            'id' => $this->primaryKey(),
            'fullName' => $this->string()->notNull(),
            'emailAddress' => $this->string()->notNull(),
            'question' => $this->text()->notNull(),
            'status' => $this->string()->notNull()->defaultValue('pending'),
            'options' => $this->text(),
            'isNew' => $this->boolean()->notNull()->defaultValue(true),
            'abuse' => $this->boolean()->notNull()->defaultValue(false),
            'hasPurchased' => $this->boolean()->defaultValue(false),
            'votes' => $this->integer(),
            'displayId' => $this->integer(),
            'elementId' => $this->integer()->notNull(),
            'sectionId' => $this->integer(),
            'structureId' => $this->integer(),
            'productTypeId' => $this->integer(),
            'geolocation' => $this->text(),
            'ipAddress' => $this->string()->notNull(),
            'userAgent' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
        $this->createTable(Table::QUESTIONSANSWERS, [
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
        $this->createTable(Table::QUESTIONSANSWERSCOMMENTS, [
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
        $this->createTable(Table::DISPLAYS, [
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
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
        $this->createTable(Table::CORRESPONDENCE, [
            'id' => $this->primaryKey(),
            'email' => $this->text(),
            'response' => $this->text(),
            'allowReplies' => $this->boolean()->defaultValue(false),
            'password' => $this->string()->notNull(),
            'ownerEmail' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'emailTemplateId' => $this->integer()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
        $this->createTable(Table::CORRESPONDENCERESPONSES, [
            'id' => $this->primaryKey(),
            'response' => $this->text()->notNull(),
            'parentId' => $this->integer()->notNull(),
            'helpfulResponse' => $this->integer(),
            'isNew' => $this->boolean()->defaultValue(true),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
        $this->createTable(Table::NOTES, [
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

        // Direct Links
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

        // Email Templates
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
    }
    /**
     * Create indexes
     */
    protected function createIndexes()
    {
        $this->createIndex($this->db->getIndexName(Table::REVIEWS, 'emailAddress', false), Table::REVIEWS, 'emailAddress', false);
        $this->createIndex($this->db->getIndexName(Table::REVIEWS, 'status', false), Table::REVIEWS, 'status', false);
        $this->createIndex($this->db->getIndexName(Table::REVIEWS, 'displayId', false), Table::REVIEWS, 'displayId', false);
        $this->createIndex($this->db->getIndexName(Table::REVIEWS, 'elementId', false), Table::REVIEWS, 'elementId', false);
        $this->createIndex($this->db->getIndexName(Table::QUESTIONS, 'emailAddress', false), Table::QUESTIONS, 'emailAddress', false);
        $this->createIndex($this->db->getIndexName(Table::QUESTIONS, 'status', false), Table::QUESTIONS, 'status', false);
        $this->createIndex($this->db->getIndexName(Table::QUESTIONS, 'displayId', false), Table::QUESTIONS, 'displayId', false);
        $this->createIndex($this->db->getIndexName(Table::QUESTIONS, 'elementId', false), Table::QUESTIONS, 'elementId', false);
        $this->createIndex($this->db->getIndexName(Table::DISPLAYS, 'name', false), Table::DISPLAYS, 'name', false);
        $this->createIndex($this->db->getIndexName(Table::DISPLAYS, 'handle', false), Table::DISPLAYS, 'handle', false);
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
        $this->addForeignKey($this->db->getForeignKeyName(Table::REVIEWS, 'elementId'), Table::REVIEWS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::REVIEWSREPLIES, 'elementId'), Table::REVIEWSREPLIES, 'elementId', Table::REVIEWS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::REVIEWSREPLIES, 'authorId'), Table::REVIEWSREPLIES, 'authorId', CraftTable::USERS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONS, 'elementId'), Table::QUESTIONS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONSANSWERS, 'elementId'), Table::QUESTIONSANSWERS, 'elementId', Table::QUESTIONS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONSANSWERS, 'authorId'), Table::QUESTIONSANSWERS, 'authorId', CraftTable::USERS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONSANSWERSCOMMENTS, 'answerId'), Table::QUESTIONSANSWERSCOMMENTS, 'answerId', Table::QUESTIONSANSWERS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::QUESTIONSANSWERSCOMMENTS, 'authorId'), Table::QUESTIONSANSWERSCOMMENTS, 'authorId', CraftTable::USERS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::DISPLAYS, 'fieldLayoutId'), Table::DISPLAYS, 'fieldLayoutId', CraftTable::FIELDLAYOUTS, 'id', 'SET NULL', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::NOTES, 'elementId'), Table::NOTES, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::CORRESPONDENCERESPONSES, 'parentId'), Table::CORRESPONDENCERESPONSES, 'parentId', Table::CORRESPONDENCE, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::RULESFLAGGED, 'ruleId'), Table::RULESFLAGGED, 'ruleId', Table::RULES, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'elementId'), Table::DIRECTLINKS, 'elementId', CraftTable::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(Table::DIRECTLINKS, 'userId'), Table::DIRECTLINKS, 'userId', CraftTable::USERS, 'id', 'CASCADE', null);
    }
    /**
     * Remove tables
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(Table::QUESTIONSANSWERSCOMMENTS);
        $this->dropTableIfExists(Table::QUESTIONSANSWERS);
        $this->dropTableIfExists(Table::REVIEWSREPLIES);
        $this->dropTableIfExists(Table::REVIEWS);
        $this->dropTableIfExists(Table::QUESTIONS);
        $this->dropTableIfExists(Table::DISPLAYS);
        $this->dropTableIfExists(Table::NOTES);
        $this->dropTableIfExists(Table::CORRESPONDENCERESPONSES);
        $this->dropTableIfExists(Table::CORRESPONDENCE);
        $this->dropTableIfExists(Table::RULESFLAGGED);
        $this->dropTableIfExists(Table::RULES);
        $this->dropTableIfExists(Table::DIRECTLINKS);
        $this->dropTableIfExists(Table::EMAIL_TEMPLATES);
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