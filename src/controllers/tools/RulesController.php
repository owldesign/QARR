<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\controllers\tools;

use craft\helpers\Json;
use craft\helpers\StringHelper;
use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class RulesController
 * @package owldesign\qarr\controllers
 */
class RulesController extends Controller
{
    // Protected Properties
    // =========================================================================

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    public function actionIndex(array $variables = []): Response
    {
        $variables['rules'] = QARR::$plugin->rules->getAllRules();

        return $this->renderTemplate('qarr/tools/rules/index', $variables);
    }

    public function actionEdit(int $ruleId = null, Rule $rule = null): Response
    {
        $variables = [
            'ruleId' => $ruleId,
            'brandNewRule' => false
        ];

        if ($ruleId !== null) {
            if ($rule === null) {
                $rule = QARR::$plugin->rules->getRuleById($ruleId);
                $variables['rule'] = $rule;
                if ($rule->options) {
                    $variables['rule']['options'] = Json::decode($rule->options);
                }

                if (!$rule) {
                    throw new NotFoundHttpException(QARR::t('Rule not found'));
                }
            }
        } else {
            if ($rule === null) {
                $rule = new Rule();
                $variables['brandNewRule'] = true;
            }
        }

        $this->_enforceEditRulePermissions($rule);
        $variables['fullPageForm'] = true;
        $variables['continueEditingUrl'] = 'qarr/tools/rules/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/tools/rules/_edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $model = new Rule();
        $model->id = $request->getBodyParam('ruleId');
        $model->name = $request->getBodyParam('name');
        $model->handle = $request->getBodyParam('handle');
        $model->enabled = $request->getBodyParam('enabled');
        $model->icon = $request->getBodyParam('icon');

        if ($request->getBodyParam('data') != '' && $request->getBodyParam('data') != '[]') {
            $model->data = Json::decode($request->getBodyParam('data'));
            $model->data = StringHelper::toString($model->data);
        }

        if ($request->getBodyParam('options')) {
            $model->options = Json::encode($request->getBodyParam('options'));
        }

        if ($request->getBodyParam('settings')) {
            $model->settings = Json::encode($request->getBodyParam('settings'));
        }

        // Permission enforcement
        $this->_enforceEditRulePermissions($model);

        if (!QARR::$plugin->rules->saveRule($model)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $model->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(QARR::t('Couldn’t save rule.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'rule' => $model
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(QARR::t('Rule saved.'));

        return $this->redirectToPostedUrl($model);
    }

    /**
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $this->requirePermission('qarr:deleteDisplays');

        $displayId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        QARR::$plugin->displays->deleteDisplayById($displayId);

        return $this->asJson(['success' => true]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Rule $rule
     * @throws \yii\web\ForbiddenHttpException
     */
    private function _enforceEditRulePermissions(Rule $rule)
    {
        $this->requirePermission('qarr:editRules');
    }

    /**
     * @return Rule
     * @throws NotFoundHttpException
     */
    private function _getRuleModel(): Rule
    {
        $ruleId = Craft::$app->getRequest()->getBodyParam('ruleId');

        if ($ruleId) {
            $rule = QARR::$plugin->displays->getDisplayById($ruleId);

            if (!$rule) {
                throw new NotFoundHttpException('Rule not found');
            }
        } else {
            $display = new Rule();
        }

        return $rule;
    }

    /**
     * @param Rule $rule
     */
    private function _populateRuleModel(Rule $rule)
    {
        $request = Craft::$app->getRequest();

        $rule->name = $request->getBodyParam('name', $rule->name);
        $rule->handle = $request->getBodyParam('handle', $rule->handle);
        $rule->options = $request->getBodyParam('options', $rule->options);
        $rule->settings = $request->getBodyParam('settings', $rule->settings);
    }


}
