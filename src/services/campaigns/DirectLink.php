<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\services\campaigns;

use owldesign\qarr\QARR;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use owldesign\qarr\models\DirectLink as DirectLinkModel;
use owldesign\qarr\records\DirectLink as DirectLinkRecord;
use Hashids\Hashids;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\db\Query;
use owldesign\qarr\rules\RuleChecker;
use yii\base\Exception;

/**
 * Class Reviews
 * @package owldesign\qarr\services
 */
class DirectLink extends Component
{
    // Protected Properties
    // =========================================================================

    // Properties
    // =========================================================================
    /**
     * @var
     */
    protected $linkRecord;
    /**
     * @var
     */
    private $_allLink;
    /**
     * @var
     */
    private $_linksById;
    /**
     * @var bool
     */
    private $_fetchedAllLinks = false;

    // Public Methods
    // =========================================================================

    /**
     * Get all links
     *
     * @param null $enabled
     * @return array
     */
    public function getAllLinks($enabled = null): array
    {
        if ($this->_fetchedAllLinks) {
            return array_values($this->_linksById);
        }

        $query = $this->_createLinkQuery();

        if ($enabled) {
            $query->where(['enabled' => $enabled]);
        }

        $results = $query->all();

        $this->_linksById = [];

        foreach ($results as $result) {
            $result['options'] = Json::decode($result['options']);
            $result['settings'] = Json::decode($result['settings']);

            $link = new DirectLinkModel($result);
            $this->_linksById[$link->id] = $link;
        }

        $this->_fetchedAllLinks = true;

        return array_values($this->_linksById);
    }


    /**
     * @param int $id
     * @return DirectLinkModel
     */
    public function getLinkById(int $id)
    {
        $query = DirectLinkRecord::find()
            ->where(['id' => $id]);

        $record = $query->one();

        $record = new DirectLinkModel($record->toArray(['id', 'slug', 'elementId', 'userId', 'type', 'link', 'completed', 'enabled', 'settings', 'options', 'dateCreated', 'dateUpdated']));


        return $record;
    }

    /**
     * Save direct link
     *
     * @param DirectLinkModel $link
     * @return object
     * @throws Exception
     */
    public function save(DirectLinkModel $link): bool
    {
        $isNewLink = !$link->id;

        if ($link->id) {
            $record = DirectLinkRecord::findOne($link->id);

            if (!$record) {
                throw new Exception(QARR::t('Direct link with ID not found: ' . $link->id));
            }
        } else {
            $record = new DirectLinkRecord();
        }

        $record->slug       = $link->slug;
        $record->elementId  = $link->elementId;
        $record->userId     = $link->userId;
        $record->type       = $link->type;
        $record->enabled    = $link->enabled;
        $record->completed  = $link->completed;


        if ($link->settings) {
            $record->settings = $link->settings;
        }

        if ($link->options) {
            $record->options = $link->options;
        }

        $record->save(false);

        if ($isNewLink) {
            $link->id = $record->id;
        }

        return true;
    }

    /**
     * Delete link by id
     *
     * @param $id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteLinkById($id)
    {
        $record = DirectLinkRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$record) {
            return true;
        }

        $record->delete();

        return true;
    }

    /**
     * Mark completed + add submission id
     *
     * @param $id
     * @param $submission
     * @return bool
     */
    public function completeById($id, $submission)
    {
        try {
            $record = DirectLinkRecord::find()
                ->where(['id' => $id])
                ->one();

            $record->completed = true;

            $record->options = Json::encode([
                'submissionId' => $submission->id
            ]);

            $record->save(false);
        } catch (\Exception $e) {
            QARR::log('There was an error completing direct link: ' . $e->getMessage());
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Create direct link query
     *
     * @return Query
     */
    private function _createLinkQuery(): Query
    {
        return (new Query())
            ->select([
                'links.id',
                'links.slug',
                'links.elementId',
                'links.userId',
                'links.type',
                'links.enabled',
                'links.completed',
                'links.settings',
                'links.options',
            ])
            ->from(['{{%qarr_direct_links}} links'])
            ->orderBy(['id' => SORT_ASC]);
    }
}
