<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\controllers\settings;

use owldesign\qarr\QARR;
use owldesign\qarr\jobs\GeolocationTask;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class RulesController
 * @package owldesign\qarr\controllers
 */
class UtilitiesController extends Controller
{
    // Protected Properties
    // =========================================================================

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Settings index
     *
     * @param array $variables
     * @return Response
     */
    public function actionIndex(array $variables = []): Response
    {
        $variables['rules'] = QARR::$plugin->rules->getAllRules();

        return $this->renderTemplate('qarr/settings/utilities/index', $variables);
    }

    /**
     * Update Geolocation for all entries
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateGeolocations()
    {
        $this->requirePostRequest();

        // TODO: allow to pick Reviews or Questions

        $reviews = QARR::$plugin->elements->queryElements('reviews')->all();
        $questions = QARR::$plugin->elements->queryElements('questions')->all();

        foreach($reviews as $element) {
            if ($element) {
                if (!$element->geolocation || $element->geolocation == '') {
                    Craft::$app->getQueue()->push(new GeolocationTask([
                        'ipAddress' => $element->ipAddress,
                        'elementId' => $element->id,
                        'table' => '{{%qarr_reviews}}'
                    ]));
                }
            }
        }

        foreach($questions as $element) {
            if ($element) {
                if (!$element->geolocation || $element->geolocation == '') {
                    Craft::$app->getQueue()->push(new GeolocationTask([
                        'ipAddress' => $element->ipAddress,
                        'elementId' => $element->id,
                        'table' => '{{%qarr_reviews}}'
                    ]));
                }
            }
        }

        return $this->asJson([
            'success'   => true,
            'message'   => QARR::t('Geolocation updates started...'),
        ]);
    }

}
