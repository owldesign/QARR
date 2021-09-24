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

use craft\helpers\DateTimeHelper;
use owldesign\qarr\QARR;
use owldesign\qarr\jobs\GeolocationTask;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;
use owldesign\qarr\records\Question;
use owldesign\qarr\records\Review;
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

        $reviews = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Review')->all();
        $questions = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Question')->all();

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

    /**
     * Fix review elements
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFixReviewElements()
    {
        $this->requirePostRequest();
        $date = DateTimeHelper::toDateTime(DateTimeHelper::currentTimeStamp());
        $reviews = Review::find()->where(['dateDeleted' => null])->all();

        foreach ($reviews as $review) {
            $element = Craft::$app->getElements()->getElementById($review->elementId);

            if (!$element) {
                $review->updateAttributes(['dateDeleted' => $date->format('Y-m-d H:i:s')]);
            }
        }

        return true;
    }

    /**
     * Fix question elements
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionFixQuestionElements()
    {
        $this->requirePostRequest();
        $date = DateTimeHelper::toDateTime(DateTimeHelper::currentTimeStamp());
        $questions = Question::find()->where(['dateDeleted' => null])->all();

        foreach ($questions as $question) {
            $element = Craft::$app->getElements()->getElementById($question->elementId);

            if (!$element) {
                $question->updateAttributes(['dateDeleted' => $date->format('Y-m-d H:i:s')]);
            }
        }

        return true;
    }

}
