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

    public function actionGetElementSettingsModal()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $this->requirePermission('qarr:manageSettings');

        $settings = QARR::$plugin->settings;
        $sections = Craft::$app->getSections()->getAllSections();

        foreach ($sections as $section) {
            if ($section->type == 'single') {
                $variables['sections']['single'][] = $section;
            }
            if ($section->type == 'channel') {
                $variables['sections']['channel'][] = $section;
            }
        }

        $commerce = Craft::$app->getPlugins()->isPluginEnabled('commerce');
        if ($commerce) {
            $variables['sections']['product'] = CommercePlugin::getInstance()->productTypes->getAllProductTypes();
        }

        $variables['settings'] = $settings;

        $template = Craft::$app->view->renderTemplate('qarr/settings/_includes/element-index-assets', $variables);

        return $this->asJson([
            'success' => true,
            'template'   => $template
        ]);
    }

    public function actionSaveElementSettings()
    {
        $this->requirePostRequest();

        $this->requirePermission('qarr:manageSettings');

        $pluginHandle = Craft::$app->getRequest()->getRequiredBodyParam('pluginHandle');
        $settings = Craft::$app->getRequest()->getBodyParam('settings', []);
        $plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

        if ($plugin === null) {
            throw new NotFoundHttpException('Plugin not found');
        }

        $pluginSettings = QARR::$plugin->settings;

        $result = Craft::$app->getPlugins()->savePluginSettings($plugin, $settings);

        return $this->asJson([
            'success' => $result,
        ]);
    }

}
