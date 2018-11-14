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

use craft\helpers\Json;
use owldesign\qarr\models\Flagged;
use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\records\Review as ReviewRecord;
use owldesign\qarr\records\Flagged as FlaggedRecord;
use owldesign\qarr\records\Rule as RuleRecord;
use owldesign\qarr\rules\ProfanityCheck;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\db\Query;
use yii\base\Exception;

/**
 * Class Reviews
 * @package owldesign\qarr\services
 */
class Rules extends Component
{
    // Properties
    // =========================================================================
    /**
     * @var
     */
    protected $ruleRecord;
    /**
     * @var
     */
    private $_allRules;
    /**
     * @var
     */
    private $_rulesById;
    /**
     * @var bool
     */
    private $_fetchedAllRules = false;

    // Public Methods
    // =========================================================================


    public function getAllRules(): array
    {
        if ($this->_fetchedAllRules) {
            return array_values($this->_rulesById);
        }

        $results = $this->_createRuleQuery()->all();

        $this->_rulesById = [];

        foreach ($results as $result) {
            $rule = new Rule($result);
            $this->_rulesById[$rule->id] = $rule;
        }

        $this->_fetchedAllRules = true;

        return array_values($this->_rulesById);
    }

    /**
     * Check profanity rule
     *
     * @param $string
     * @param $entry
     * @throws \Throwable
     */
    public function checkProfanity($string, $entry)
    {
        // Get user defined data
        $newData = Craft::$app->config->getConfigFromFile('qarr');
        $profanities = [];
        if (isset($newData['rules']['profanity']['data'])) {
            foreach ($newData['rules']['profanity']['data'] as $word) {
                ArrayHelper::prependOrAppend($profanities, $word, true);
            }
        }
        $profanityCheck = new ProfanityCheck($profanities);
        $hasProfanity = $profanityCheck->filter($string, true);
        
        if ($hasProfanity['hasMatch']) {
            $this->flagElement(1, $entry->id, $hasProfanity);
        }
    }

    /**
     * @param int $ruleId
     * @param int $elementId
     * @param null $details
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function flagElement(int $ruleId, int $elementId, $details = null): bool
    {
        $record = new FlaggedRecord();
        $record->ruleId = $ruleId;
        $record->elementId = $elementId;
        if ($details) {
            $record->details = Json::encode($details);
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    public function getFlagged($elementId)
    {
        $flags = [];

        $query = FlaggedRecord::find()
            ->where(['elementId' => $elementId]);

        $records = $query->all();

        if (!$records) {
            return null;
        }

        foreach($records as $key => $record) {
            $details = Json::decode($record->details);
            $flags[$key] = new Flagged($record->toArray(['id', 'ruleId', 'elementId', 'details', 'dateCreated', 'dateUpdated']));
            $flags[$key]['details'] = $details;
            $flags[$key]['rule'] = $this->getRuleById($record->ruleId);
        }

        return $flags;
    }

    /**
     * @param int $id
     * @return array|null|Rule|\yii\db\ActiveRecord
     */
    public function getRuleById(int $id)
    {
        $query = RuleRecord::find()
            ->where(['id' => $id]);

        $record = $query->one();

        $record = new Rule($record->toArray(['id', 'name', 'handle', 'settings', 'options', 'dateCreated', 'dateUpdated']));

        return $record;
    }

    /**
     * Save rule
     *
     * @param Rule $rule
     * @return bool
     * @throws Exception
     */
    public function saveRule(Rule $rule): bool
    {
        $isNewRule = !$rule->id;

        if ($rule->id) {
            $record = RuleRecord::findOne($rule->id);

            if  (!$record) {
                throw new Exception(QARR::t('Rule with ID not found: '.$rule->id));
            }
        } else {
            $record = new RuleRecord();
        }

        $record->name       = $rule->name;
        $record->handle     = $rule->handle;
        $record->settings   = $rule->settings;
        $record->options    = $rule->options;
        $record->save(false);

        if ($isNewRule) {
            $rule->id = $record->id;
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    private function _createRuleQuery(): Query
    {
        return (new Query())
            ->select([
                'rules.id',
                'rules.name',
                'rules.handle',
                'rules.settings',
                'rules.options',
            ])
            ->from(['{{%qarr_rules}} rules'])
            ->orderBy(['id' => SORT_ASC]);
    }
}
