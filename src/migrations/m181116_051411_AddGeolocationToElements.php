<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;

use owldesign\qarr\elements\db\Table;

/**
 * m181116_051411_AddGeolocationToElements migration.
 */
class m181116_051411_AddGeolocationToElements extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::REVIEWS, 'geolocation', $this->text()->after('productTypeId'));
        $this->addColumn(Table::QUESTIONS, 'geolocation', $this->text()->after('productTypeId'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181116_051411_AddGeolocationToElements cannot be reverted.\n";
        return false;
    }
}
