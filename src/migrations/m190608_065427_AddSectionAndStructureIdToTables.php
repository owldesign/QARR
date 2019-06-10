<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;
use owldesign\qarr\elements\db\Table;

/**
 * m190608_065427_AddSectionAndStructureIdToTables migration.
 */
class m190608_065427_AddSectionAndStructureIdToTables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::REVIEWS, 'sectionId', $this->integer());
        $this->addColumn(Table::QUESTIONS, 'sectionId', $this->integer());

        $this->addColumn(Table::REVIEWS, 'structureId', $this->integer());
        $this->addColumn(Table::QUESTIONS, 'structureId', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
