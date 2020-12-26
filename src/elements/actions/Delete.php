<?php

namespace owldesign\qarr\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use owldesign\qarr\elements\Display;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Review;
use owldesign\qarr\QARR;

class Delete extends ElementAction
{
    // Properties
    // =========================================================================
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;
    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;
    // Public Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return QARR::t('Delete…');
    }
    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }
    // Public Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return QARR::t('Are you sure you want to delete the selected entries?');
    }
    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;
        $response = null;


        if ($query->elementType === Review::class) {
            $response = QARR::$plugin->reviews->deleteEntries($query->all());
        } elseif ($query->elementType === Display::class) {
            $response = QARR::$plugin->displays->deleteEntries($query->all());
        } elseif ($query->elementType === Question::class) {
            $response = QARR::$plugin->questions->deleteEntries($query->all());
        }

        if ($response) {
            $message = QARR::t('Entries Deleted.');
        } else {
            $message = QARR::t('Failed to delete entry.');
        }

        $this->setMessage($message);

        return $response;
    }
}