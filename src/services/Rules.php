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
use craft\helpers\StringHelper;
use owldesign\qarr\models\Flagged;
use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;
use owldesign\qarr\records\Flagged as FlaggedRecord;
use owldesign\qarr\records\Rule as RuleRecord;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\db\Query;
use owldesign\qarr\rules\RuleChecker;
use Throwable;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

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
    private bool $_fetchedAllRules = false;

    // Public Methods
    // =========================================================================


    /**
     * Get all rules
     *
     * @param null $enabled
     * @return array
     */
    public function getAllRules($enabled = null): array
    {
        if ($this->_fetchedAllRules) {
            return array_values($this->_rulesById);
        }

        $query = $this->_createRuleQuery();
        if ($enabled) {
            $query->where(['enabled' => $enabled]);
        }
        $results = $query->all();

        $this->_rulesById = [];

        foreach ($results as $result) {
            $result['options'] = Json::decode($result['options']);
            $result['settings'] = Json::decode($result['settings']);
            $rule = new Rule($result);
            $this->_rulesById[$rule->id] = $rule;
        }

        $this->_fetchedAllRules = true;

        return array_values($this->_rulesById);
    }

    /**
     * Apply rules
     *
     * @param $element
     * @param $elementType
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function applyRules($element, $elementType): void
    {
        $rules = $this->getAllRules(true);
        $config = Craft::$app->config->getConfigFromFile('qarr');

        // Check config data
        if ($rules && isset($config['rules'])) {
            foreach ($config['rules'] as $key => $words) {
                foreach ($rules as $index => $rule) {
                    if ($rule->handle === $key) {
                        $data = StringHelper::split($rule->data);
                        $newWords = StringHelper::split($words);

                        if ($newWords) {
                            foreach ($newWords as $word) {
                                ArrayHelper::prependOrAppend($data, $word, false);
                            }
                        }

                        $rules[$index]->data = $data;
                    }
                }
            }
        }

        // Ok lets go
        $this->performRules($element, $elementType, $rules);
    }

    /**
     * Perform rule matching
     *
     * @param $element
     * @param $elementType
     * @param $rules
     * @return bool
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function performRules($element, $elementType, $rules): bool
    {
        foreach ($rules as $rule) {
            if (is_string($rule->data)) {
                $data = StringHelper::explode($rule->data, ',', true, true);
            } else {
                $data = $rule->data;
            }

            $checker = new RuleChecker($data);

            if ($elementType === 'question') {
                $result = $checker->filter($element['question'], true);
            } else {
                $result = $checker->filter($element['feedback'], true);
            }

            if ($result['hasMatch']) {
                $this->flagElement($rule->id, $element['id'], $result);
            }
        }

        return true;
    }

    /**
     * @param int $ruleId
     * @param int $elementId
     * @param null $details
     * @return bool
     * @throws Throwable
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
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Get flagged element by id
     *
     * @param $elementId
     * @return array|null
     */
    public function getFlagged($elementId): ?array
    {
        $flags = [];

        $query = FlaggedRecord::find()
            ->where(['elementId' => $elementId]);

        $records = $query->all();

        if (!$records) {
            return null;
        }

        foreach ($records as $key => $record) {
            $details = Json::decode($record->details);
            $flags[$key] = new Flagged($record->toArray(['id', 'ruleId', 'elementId', 'details', 'dateCreated', 'dateUpdated']));
            $flags[$key]['details'] = $details;
            $flags[$key]['rule'] = $this->getRuleById($record->ruleId);
        }

        return $flags;
    }

    /**
     * @param int $id
     * @return array|null|Rule|ActiveRecord
     */
    public function getRuleById(int $id): Rule|array|ActiveRecord|null
    {
        $query = RuleRecord::find()
            ->where(['id' => $id]);

        $record = $query->one();

        return new Rule($record->toArray(['id', 'name', 'handle', 'enabled', 'data', 'icon', 'settings', 'options', 'dateCreated', 'dateUpdated']));
    }

    /**
     * @param $id
     * @return bool|int|string|null
     */
    public function getFlaggedCountByRuleId($id): bool|int|string|null
    {
        return FlaggedRecord::find()
            ->where(['ruleId' => $id])->count();
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

            if (!$record) {
                throw new Exception(QARR::t('Rule with ID not found: ' . $rule->id));
            }
        } else {
            $record = new RuleRecord();
        }

        $record->name = $rule->name;
        $record->handle = $rule->handle;
        $record->enabled = $rule->enabled;
        $record->data = $rule->data;
        $record->icon = $rule->icon;

        if ($rule->settings) {
            $record->settings = $rule->settings;
        }

        if ($rule->options) {
            $record->options = $rule->options;
        }

        $record->save(false);

        if ($isNewRule) {
            $rule->id = $record->id;
        }

        return true;
    }

    /**
     * Delete rule by id
     *
     * @param $id
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteRuleById($id): bool
    {
        $record = RuleRecord::find()
            ->where(['id' => $id])
            ->one();

        if (!$record) {
            return true;
        }

        // Delete all flagged records
        if ($this->removeRecord($id)) {
            $record->delete();
        }

        return true;
    }

    /**
     * Remove flagged records
     *
     * @param $elementId
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function removeRecord($elementId): bool
    {
        $records = FlaggedRecord::find()
            ->where(['elementId' => $elementId])
            ->all();

        if (!$records) {
            return true;
        }

        foreach ($records as $record) {
            $record->delete();
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Create rule query
     *
     * @return Query
     */
    private function _createRuleQuery(): Query
    {
        return (new Query())
            ->select([
                'rules.id',
                'rules.name',
                'rules.handle',
                'rules.enabled',
                'rules.data',
                'rules.icon',
                'rules.settings',
                'rules.options',
            ])
            ->from(['{{%qarr_rules}} rules'])
            ->orderBy(['id' => SORT_ASC]);
    }
}
