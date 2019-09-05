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
use craft\commerce\Plugin as CommercePlugin;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class RulesController
 * @package owldesign\qarr\controllers
 */
class ConfigurationController extends Controller
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
     */
    public function actionIndex(array $variables = [])
    {
        $settings = QARR::$plugin->settings;
        $sections = Craft::$app->getSections()->getAllSections();

        foreach ($sections as $section) {
            if ($section->type == 'single') {
                $variables['sections']['singles'][] = $section;
            }
            if ($section->type == 'channel') {
                $variables['sections']['channels'][] = $section;
            }
        }

        $commerce = Craft::$app->getPlugins()->isPluginEnabled('commerce');
        if ($commerce) {
            $variables['sections']['products'] = CommercePlugin::getInstance()->productTypes->getAllProductTypes();
        }

        $variables['settings'] = $settings;

        QARR::$plugin->routeTemplate('settings/configuration/index', $variables);
    }

}
