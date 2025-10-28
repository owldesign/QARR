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
class EmailTemplatesController extends Controller
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

        QARR::$plugin->routeTemplate('settings/emails/index', $variables);
    }

}
