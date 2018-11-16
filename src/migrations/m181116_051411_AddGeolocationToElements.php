<?php

namespace owldesign\qarr\migrations;

use Craft;
use craft\db\Migration;

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
        $this->addColumn('{{%qarr_reviews}}', 'geolocation', $this->text()->after('productTypeId'));
        $this->addColumn('{{%qarr_questions}}', 'geolocation', $this->text()->after('productTypeId'));
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
