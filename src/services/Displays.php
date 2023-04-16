<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\services;

use craft\helpers\ArrayHelper;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Display;
use owldesign\qarr\records\Display as DisplayRecord;
use owldesign\qarr\records\Review as ReviewRecord;

use Craft;
use craft\db\Query;
use craft\base\Component;
use craft\helpers\Json;
use yii\base\Exception;

/**
 * Class Displays
 * @package owldesign\qarr\services
 */
class Displays extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    private $_displays;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function getAllDisplays(): array
    {
        if ($this->_displays !== null) {
            return $this->_displays;
        }

        $this->_displays = [];

        $displayRecords = DisplayRecord::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        foreach ($displayRecords as $displayRecord) {
            $this->_displays[] = $this->_createDisplayFromRecord($displayRecord);
        }

        return $this->_displays;
    }

    /**
     * Returns display by its ID.
     *
     * @param int $displayId
     * @return Display|null
     */
    public function getDisplayById(int $displayId)
    {
        return ArrayHelper::firstWhere($this->getAllDisplays(), 'id', $displayId);
    }

    /**
     * Returns display by its UID.
     *
     * @param int $uid
     * @return Display|null
     */
    public function getDisplayByUid(int $uid)
    {
        return ArrayHelper::firstWhere($this->getAllDisplays(), 'uid', $uid, true);
    }

    /**
     * Returns display by its UID.
     *
     * @param string $displayHandle
     * @return Display|null
     */
    public function getDisplayByHandle(string $displayHandle)
    {
        return ArrayHelper::firstWhere($this->getAllDisplays(), 'handle', $displayHandle, true);
    }

    /**
     * @param Display $display
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveDisplay(Display $display)
    {
        $isNewDisplay = !$display->id;

        if ($display->id) {
            $record = DisplayRecord::findOne($display->id);

            if (!$record) {
                throw new Exception(QARR::t('No display exists with id '.$display->id));
            }
        }

        $display->validate();

        if ($display->hasErrors()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $fieldLayout = $display->getFieldLayout();
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $display->fieldLayoutId = $fieldLayout->id;

            $success = Craft::$app->getElements()->saveElement($display);

            if (!$success) {
                QARR::error('Couldn’t save Display Element.');
                $transaction->rollBack();

                return false;
            }

            QARR::info('Display Element Saved.');
            $transaction->commit();

        } catch (\Exception $e) {
            QARR::error('Failed to save element: '.$e->getMessage());
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param int $displayId
     * @return bool
     * @throws \Exception
     */
    public function deleteDisplayById(int $displayId): bool
    {
        $display = $this->getDisplayById($displayId);

        if (!$display) {
            return false;
        }

        return $this->delete($display);
    }

    /**
     * @param Display $display
     * @return bool
     * @throws \Exception
     */
    public function delete(Display $display): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getFields()->deleteLayoutById($display->fieldLayoutId);


            Craft::$app->getDb()->createCommand()
                ->softDelete('{{%qarr_displays}}', ['id' => $display->id])
                ->execute();

            // null all element's displayIds
            $this->removeDisplaysFromElements($display->id);

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * @param $displayId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function removeDisplaysFromElements($displayId)
    {
        $elements = ReviewRecord::find()->where(['displayId' => $displayId])->all();

        if (!$elements) {
            return true;
        }

        foreach ($elements as $element) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%qarr_reviews}}', ['displayId' => null], ['id' => $element->id])
                ->execute();
        }

        return true;
    }

    /**
     * @param $entries
     * @return bool
     */
    public function deleteEntries($entries)
    {
        foreach ($entries as $key => $entry) {
            $entry = $this->getDisplayById($entry->id);

            if ($entry) {
                QARR::$plugin->displays->deleteEntry($entry);
            } else {
                QARR::error("Can't delete entry with id: {$entry->id}");
            }
        }
        return true;
    }

    // Private Methods
    // =========================================================================

    private function _createDisplayFromRecord(DisplayRecord $displayRecord = null)
    {
        if (!$displayRecord) {
            return null;
        }

        $display = new Display($displayRecord->toArray([
            'id',
            'name',
            'handle',
            'titleFormat',
            'fieldLayoutId',
            'enabled',
            'uid'
        ]));

        return $display;
    }

    /**
     * Returns a Query object prepped for retrieving sections.
     *
     * @return Query
     */
    private function _createDisplayQuery(): Query
    {
        return (new Query())
            ->select([
                'displays.id',
                'displays.name',
                'displays.handle',
                'displays.fieldLayoutId',
                'displays.titleFormat',
                'displays.options',
                'displays.settings',
            ])
            ->from(['{{%qarr_displays}} displays'])
            ->orderBy(['name' => SORT_ASC]);
    }

    /**
     * @param $displayHandle
     * @return Query
     */
    private function _getDisplayByHandle($displayHandle): Query
    {
        return (new Query())
            ->select([
                'displays.id',
                'displays.name',
                'displays.handle',
                'displays.fieldLayoutId',
                'displays.titleFormat',
                'displays.options',
                'displays.settings',
            ])
            ->from(['{{%qarr_displays}} displays'])
            ->where(['handle' => $displayHandle])
            ->orderBy(['name' => SORT_ASC]);
    }
}
