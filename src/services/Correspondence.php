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
use owldesign\qarr\models\Reply;
use owldesign\qarr\records\Correspondence as CorrespondenceRecord;


use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\mail\Message;
use yii\base\Exception;

/**
 * Class Correspondence
 * @package owldesign\qarr\services
 */
class Correspondence extends Component
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @param $email
     * @param $type
     * @param $elementId
     * @return array|null
     */
    public function getCorrespondenceByParams($email, $type, $elementId)
    {
        $record = CorrespondenceRecord::find()
            ->where(['ownerEmail' => $email, 'type' => $type, 'elementId' => $elementId])
            ->all();

        if (!$record) {
            return null;
        }

        $correspondence = ArrayHelper::toArray($record);

        return $correspondence;
    }

    /**
     * @param $variables
     * @param $entry
     * @param $html
     * @param $subject
     * @param null $mail
     * @param array $attachments
     * @return bool
     * @throws \Throwable
     */
    public function sendMail($variables, $entry, $html, $subject, $mail = null, array $attachments = array()): bool
    {
        // Save Record First
        $saved = $this->save($variables, $entry);

        if (!$saved) {
            return false;
        }

        $settings = Craft::$app->systemSettings->getSettings('email');
        $message = new Message();

        $message->setFrom([$settings['fromEmail'] => $settings['fromName']]);
        $message->setTo($mail);
        $message->setSubject($subject);
        $message->setHtmlBody($html);

        return Craft::$app->mailer->send($message);
    }

    // Private Methods
    // =========================================================================

    /**
     * Save correspondence
     *
     * @param $variables
     * @param $entry
     * @return bool
     * @throws \Throwable
     */
    protected function save($variables, $entry): bool
    {
        $record = new CorrespondenceRecord();

        $record->email          = $variables['message'];
        $record->subject        = $variables['subject'];
        $record->allowReplies   = $variables['allowReplies'];
        $record->password       = $variables['password'];
        $record->type           = $variables['type'];
        if (isset($variables['emailTemplateId'])) {
            $record->emailTemplateId = $variables['emailTemplateId'];
        }
        $record->elementId      = $entry->id;
        $record->ownerEmail     = $entry->emailAddress;

        $record->validate();

        if ($record->hasErrors()) {
            return false;
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

    public function deleteCorrespondenceByElement($element)
    {
        $records = CorrespondenceRecord::find()
            ->where(['elementId' => $element->id])
            ->all();

        if (!$records) {
            return true;
        }

        foreach ($records as $record) {
            $record->delete();
        }

        return true;
    }


}
