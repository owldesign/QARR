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

use craft\queue\QueueInterface;
use Exception;
use owldesign\qarr\QARR;
use owldesign\qarr\helpers\Countries;

use Craft;
use craft\helpers\Json;
use craft\queue\BaseJob;
use yii\queue\Queue;

class GeolocationTask extends BaseJob
{
    // Public Properties
    // =========================================================================

    public string $ipAddress;
    public int $elementId;
    public $table;

    // Public Methods
    // =========================================================================

    /**
     * @param QueueInterface|Queue $queue
     * @return void
     */
    public function execute($queue): void
    {
        try {
            $json = file_get_contents('http://ip-api.com/json/'.$this->ipAddress);

            if (!$json || Json::decode($json)['status'] === 'fail') {
                $json = file_get_contents('http://api.ipstack.com/'. $this->ipAddress .'?access_key=52190a7c005443842c6b11c70df7f59c&format=1&fields=country_code,country_name,region_name,city,zip');
            }

            $geolocation = $this->_normalizeData($json);

            QARR::log('Geolocation data: ' . Json::encode($geolocation));


            $result = Craft::$app->getDb()->createCommand()
                ->update($this->table, ['geolocation' => Json::encode($geolocation)], ['id' => $this->elementId])
                ->execute();

        } catch (Exception $e) {
            QARR::log('Geolocation failed to update: ' . $e->getMessage());
        }
    }

    // Private Methods
    // =========================================================================

    private function _normalizeData($json): array
    {
        $cleanData = [];
        $data = Json::decode($json);

        if (array_key_exists('countryCode', $data)) {
            $cleanData['country_code'] = $data['countryCode'];
        } else {
            $cleanData['country_code'] = $data['country_code'];
        }

        if (array_key_exists('country', $data)) {
            $cleanData['country_name'] = $data['country'];
        } else {
            $cleanData['country_name'] = $data['country_name'];
        }

        if (array_key_exists('regionName', $data)) {
            $cleanData['region'] = $data['regionName'];
        } else {
            $cleanData['region'] = $data['region_name'];
        }

        $cleanData['city'] = $data['city'];

        if (array_key_exists('zip', $data)) {
            $cleanData['postal'] = $data['zip'];
        }

        // Apply continent to geolocation
        if ($cleanData['country_code']) {
            $countries = Countries::instance()->countries;

            foreach ($countries as $code => $country) {
                if ($code === $cleanData['country_code']) {
                    $cleanData['continent'] = $country['continent'];
                }
            }
        }

        return $cleanData;
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
