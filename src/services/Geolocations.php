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
use owldesign\qarr\elements\Question;
use owldesign\qarr\models\Flagged;
use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\records\Review as ReviewRecord;
use owldesign\qarr\records\Flagged as FlaggedRecord;
use owldesign\qarr\records\Rule as RuleRecord;
use owldesign\qarr\helpers\Countries;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\db\Query;
use owldesign\qarr\rules\RuleChecker;
use yii\base\Exception;
use yii\caching\Cache;

require_once CRAFT_VENDOR_PATH . '/owldesign/qarr/src/functions/array-group-by.php';

/**
 * Class Reviews
 * @package owldesign\qarr\services
 */
class Geolocations extends Component
{
    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * Get geolocations data from cache
     * 
     * @return mixed
     */
    public function all()
    {
        $cache = Craft::$app->getCache();

        if ($cache->get('geolocations') === false) {
            // Fetch all records
            $reviews    = Review::find()->asArray()->all();
            $questions  = Question::find()->asArray()->all();
            $elements   = ArrayHelper::merge($reviews, $questions);
            $totalElements = count($elements);
            $geolocationArrayClean = [];
            $geolocationsArray = ArrayHelper::getColumn($elements, 'geolocation');
            if ($geolocationsArray) {
                foreach ($geolocationsArray as $geolocation) {
                    $geolocationArrayClean[] = Json::decode($geolocation);
                }

                // Group Locations
                $countryGrouped = array_group_by($geolocationArrayClean, 'country_code');
                $continentGrouped = array_group_by($geolocationArrayClean, 'continent');
                $countriesArray = Countries::instance()->countries;
                $continentsArray = Countries::instance()->continents(true);

                // Continent Grouped
                foreach ($continentGrouped as $continent => $countries) {
                    $result = [];
                    $grouped = array_group_by($countries, 'country_code');
                    foreach($grouped as $countryCode => $country) {
                        $result[$countryCode] = [
                            'country' => $country[0]['country_name'],
                            'count' => count($country),
                        ];
                    }

                    $continentGrouped[$continent] = $result;
                }

                // Country Grouped
                foreach ($countryGrouped as $countryCode => $country) {
                    $countryGrouped[$countryCode] = [
                        'country' => $country[0]['country_name'],
                        'continent' => $countriesArray[$countryCode]['continent'],
                        'count' => count($country)
                    ];
                }

                // Sort from most to least
                uasort($countryGrouped, function($a, $b) {
                    return $b['count'] <=> $a['count'];
                });

                foreach ($continentGrouped as $continent => $countries) {
                    $total = array_sum(array_map(function($item) {
                        return $item['count'];
                    }, $countries));

                    $continentGrouped[$continent] = [
                        'count' => $total,
                        'percentage' => number_format($total/$totalElements * 100) . '%',
                        'countries' => $countries
                    ];
                }
                

                $geolocations = [
                    'byContinent' => $continentGrouped,
                    'byCountry' => $countryGrouped
                ];

                // Set 24 hour cache
                $cache->set('geolocations', $geolocations);
            }
        }

        $geolocations = $cache->get('geolocations');
        
        return $geolocations;
    }

    // Private Methods
    // =========================================================================

}
