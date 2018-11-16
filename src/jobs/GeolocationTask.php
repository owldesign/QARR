<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\jobs;

use craft\helpers\Json;
use owldesign\qarr\QARR;

use Craft;
use craft\queue\BaseJob;

/**
 * QARRTask job
 *
 * Jobs are run in separate process via a Queue of pending jobs. This allows
 * you to spin lengthy processing off into a separate PHP process that does not
 * block the main process.
 *
 * You can use it like this:
 *
 * use owldesign\qarr\jobs\QARRTask as QARRTaskJob;
 *
 * $queue = Craft::$app->getQueue();
 * $jobId = $queue->push(new QARRTaskJob([
 *     'description' => Craft::t('qarr', 'This overrides the default description'),
 *     'someAttribute' => 'someValue',
 * ]));
 *
 * The key/value pairs that you pass in to the job will set the public properties
 * for that object. Thus whatever you set 'someAttribute' to will cause the
 * public property $someAttribute to be set in the job.
 *
 * Passing in 'description' is optional, and only if you want to override the default
 * description.
 *
 * More info: https://github.com/yiisoft/yii2-queue
 *
 * @author    Vadim Goncharov
 * @package   QARR
 * @since     1.0.0
 */
class GeolocationTask extends BaseJob
{
    // Public Properties
    // =========================================================================

    public $ipAddress;
    public $elementId;
    public $table;

    // Public Methods
    // =========================================================================

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     * @return bool
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        $json = file_get_contents('https://geoip-db.com/json/'.$this->ipAddress);

        $result = Craft::$app->getDb()->createCommand()
            ->update($this->table, ['geolocation' => $json], ['id' => $this->elementId])
            ->execute();

        if ($result) {
            QARR::log('Geolocation has been added to element with ID: ' . $this->elementId);
        } else {
            QARR::log('Geolocation failed to update');
        }

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('qarr', 'Geolocation');
    }
}
