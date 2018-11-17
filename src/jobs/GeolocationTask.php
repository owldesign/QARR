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
        $json = file_get_contents('http://ip-api.com/json/'.$this->ipAddress);

        if (!$json || Json::decode($json)['status'] == 'fail') {
            $json = file_get_contents('http://api.ipstack.com/'. $this->ipAddress .'?access_key=52190a7c005443842c6b11c70df7f59c&format=1&fields=country_code,country_name,region_name,city,zip');
        }
        
        $geolocation = $this->_normalizeData($json);

        $result = Craft::$app->getDb()->createCommand()
            ->update($this->table, ['geolocation' => $geolocation], ['id' => $this->elementId])
            ->execute();

        if ($result) {
            QARR::log('Geolocation has been added to element with ID: ' . $this->elementId);
        } else {
            QARR::log('Geolocation failed to update');
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    private function _normalizeData($json)
    {
        $cleanData = [];
        $data = Json::decode($json);

        if (isset($data['country_code'])) {
            if (array_key_exists($data['countryCode'])) {
                $cleanData['country_code'] = $data['countryCode'];
            } else {
                $cleanData['country_code'] = $data['country_code'];
            }

            if (array_key_exists($data['country'])) {
                $cleanData['country_name'] = $data['country'];
            } else {
                $cleanData['country_name'] = $data['country_name'];
            }

            if (array_key_exists($data['regionName'])) {
                $cleanData['region'] = $data['regionName'];
            } else {
                $cleanData['region'] = $data['region_name'];
            }

            $cleanData['city'] = $data['city'];

            if (array_key_exists($data['zip'])) {
                $cleanData['postal'] = $data['zip'];
            }

            return $cleanData;
        } else {
            return null;
        }
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
