<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\elements\actions;

use owldesign\qarr\events\SetStatusEvent;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;

use Craft;
use craft\base\Element;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Class SetStatus
 * @package owldesign\qarr\elements\actions
 */
class SetStatus extends ElementAction
{
    // Properties
    // =========================================================================

    const EVENT_AFTER_SAVE = 'eventAfterSave';

    /**
     * @var
     */
    public $status;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return QARR::t('Set Status');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['status'], 'required'];
        $rules[] = [
            ['status'],
            'in',
            'range' => ['pending', 'approved', 'rejected']
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        return Craft::$app->getView()->renderTemplate('qarr/_components/elementactions/SetStatus/trigger');
    }

    /**
     * @param ElementQueryInterface $query
     * @return bool
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $type = $this->_getType($query->elementType);

        if (!$type) {
            return false;
        }

        $response = QARR::$plugin->elements->updateAllStatuses($query->all(), $this->status, $type);

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new SetStatusEvent([
                'response' => $response,
                'status' => $this->status,
                'type' => $type
            ]));
        }

        if ($response) {
            $message = QARR::t('Status Updated.');
        } else {
            $message = QARR::t('Failed to update status.');
        }

        $this->setMessage($message);

        return $response;
    }

    /**
     * @param $element
     * @return null|string
     */
    private function _getType($element)
    {
        if ($element === Review::class) {
            return 'reviews';
        } elseif ($element === Question::class) {
            return 'questions';
        }

        return null;
    }


}