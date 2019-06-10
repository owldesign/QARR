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
    protected $displayRecord;
    /**
     * @var
     */
    private $_allDisplays;
    /**
     * @var
     */
    private $_displaysById;
    /**
     * @var bool
     */
    private $_fetchedAllDisplays = false;


    // Public Methods
    // =========================================================================

    /**
     * @param null $elementId
     * @param null $productType
     * @return array
     */
    public function getAllDisplays($elementId = null, $productType = null): array
    {
        if ($this->_fetchedAllDisplays) {
            return array_values($this->_displaysById);
        }

        $results = $this->_createDisplayQuery()->all();

        $this->_displaysById = [];

        foreach ($results as $result) {
            $display = new Display($result);
            $this->_displaysById[$display->id] = $display;
        }

        $this->_fetchedAllDisplays = true;

        return array_values($this->_displaysById);
    }

    /**
     * @param int $displayId
     * @return null|Display
     */
    public function getDisplayById(int $displayId)
    {
        if (!$displayId) {
            return null;
        }

        if ($this->_displaysById !== null && array_key_exists($displayId, $this->_displaysById)) {
            return $this->_displaysById[$displayId];
        }

        if ($this->_fetchedAllDisplays) {
            return null;
        }

        $result = $this->_createDisplayQuery()
            ->where(['id' => $displayId])
            ->one();

        return $this->_displaysById[$displayId] = $result ? new Display($result) : null;
    }


    /**
     * @param string $displayHandle
     * @return null|Display
     */
    public function getDisplayByHandle(string $displayHandle)
    {
        if (!$displayHandle) {
            return null;
        }

        $result = $this->_createDisplayQuery()
            ->where(['handle' => $displayHandle])
            ->one();

        return $result ? new Display($result) : null;
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
     * @throws \yii\db\Exception
     */
    public function delete(Display $display): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getFields()->deleteLayoutById($display->fieldLayoutId);


            Craft::$app->getDb()->createCommand()
                ->delete('{{%qarr_displays}}', ['id' => $display->id])
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
