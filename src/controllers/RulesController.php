<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\controllers;

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

    /**
     * Index
     *
     * @param array $variables
     * @return Response
     */
    public function actionIndex(array $variables = []): Response
    {
        $variables['rules'] = QARR::$plugin->rules->getAllRules();

        return $this->renderTemplate('qarr/rules/index', $variables);
    }

    /**
     * Edit
     *
     * @param int|null $ruleId
     * @param Rule|null $rule
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(int $ruleId = null, Rule $rule = null): Response
    {
        $variables = [];
        $variables['brandNewRule'] = false;

        if ($ruleId !== null) {
            if ($rule === null) {
                $rule = QARR::$plugin->rules->getRuleById($ruleId);
                if ($rule->options) {
                    $variables['rule']['options'] = Json::decode($rule->options);
                }

                if (!$rule) {
                    throw new NotFoundHttpException(QARR::t('Rule not found'));
                }
            }

            $variables['title'] = trim($rule->name) ?: QARR::t('Edit Rule');
        } else {
            if ($rule === null) {
                $rule = new Rule();
                $variables['brandNewRule'] = true;
            }

            $variables['title'] = QARR::t('Create a new rule');
        }

        $variables['ruleId'] = $ruleId;
        $variables['rule'] = $rule;


        $this->_enforceEditRulePermissions($rule);

        $variables['fullPageForm'] = true;
        $variables['continueEditingUrl'] = 'qarr/rules/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/rules/_edit', $variables);
    }

    /**
     * Save
     *
     * @return Response|null
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
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

        // Validate
        $model->validate();

        if (!$model->hasErrors() && QARR::$plugin->rules->saveRule($model)) {
            Craft::$app->getSession()->setNotice(QARR::t('Rule saved.'));
            return $this->redirectToPostedUrl($model);
        }

        Craft::$app->getSession()->setError(QARR::t('Couldn’t save rule.'));

        Craft::$app->getUrlManager()->setRouteParams([
            'rule' => $model,
            'errors' => $model->getErrors(),
        ]);




        return null;
    }

    /**
     * Delete
     *
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
