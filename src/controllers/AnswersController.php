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

use Craft;
use craft\web\Controller;
use craft\helpers\ArrayHelper;
use craft\web\View;
use yii\web\NotFoundHttpException;

use owldesign\qarr\models\Answer;
use owldesign\qarr\QARR;
use yii\web\Request;
use yii\web\Response;

class AnswersController extends Controller
{
    protected $allowAnonymous = [
        'save' => self::ALLOW_ANONYMOUS_LIVE,
        'get-hud-modal' => self::ALLOW_ANONYMOUS_LIVE,
        'perform-action' => self::ALLOW_ANONYMOUS_LIVE,
    ];

    /**
     * Create modal
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetHudModal()
    {
        $this->requirePostRequest();

        $variables = [];
        $variables['id'] = Craft::$app->getRequest()->getBodyParam('id');
        $variables['author'] = Craft::$app->getRequest()->getBodyParam('author');

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_includes/_hud', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Update status
     *
     * @return null|Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateStatus()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');
        $status = $request->getBodyParam('status');

        if (!$elementId) {
            return null;
        }

        $result = QARR::$plugin->elements->updateStatus($elementId, $status, 'questions_answers');

        if (!$result) {
            return null;
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * Update status
     *
     * @return null|Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');

        if (!$elementId) {
            return null;
        }

        $result = QARR::$plugin->answers->delete($elementId);

        if (!$result) {
            return null;
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * Save entry
     *
     * @return Response|null
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $questionId = Craft::$app->getRequest()->getBodyParam('questionId');
        $authorId = Craft::$app->getRequest()->getBodyParam('authorId');
        $anonymous = Craft::$app->getRequest()->getBodyParam('anonymous');
        $answer = Craft::$app->getRequest()->getBodyParam('answer');
        $author = Craft::$app->users->getUserById((int)$authorId);

        if (!$author) {
            $author = Craft::$app->getUser()->getIdentity();
            if (!$author) {
                return null;
            }
        }

        $model = new Answer();
        $model->answer = $answer;
        $model->elementId = $questionId;
        $model->authorId = $author->id;
        $model->anonymous = $anonymous ? 1 : null;
        $model->validate();

        if (!$model->hasErrors() && $response = QARR::$plugin->answers->save($model, $author)) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                $array = ArrayHelper::toArray($response);
                $array['author'] = $author->friendlyName;

                $oldPath = Craft::$app->view->getTemplateMode();
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
                $template = Craft::$app->view->renderTemplate('qarr/frontend/_includes/_successful-answer', $array);
                Craft::$app->view->setTemplateMode($oldPath);

                return $this->asJson([
                    'success' => true,
                    'template' => $template
                ]);
            } else {
                Craft::$app->getSession()->setNotice(QARR::t('Answer saved.'));
                return $this->redirectToPostedUrl($model);
            }
        }

        Craft::$app->getSession()->setError(QARR::t('Cannot save answer.'));

        if (Craft::$app->getRequest()->getIsAjax()) {

            return $this->asJson([
                'success' => false,
                'errors' => $model->getErrors(),
            ]);
        } else {
            Craft::$app->getUrlManager()->setRouteParams([
                'answer' => $model,
                'errors' => $model->getErrors(),
            ]);

            return null;
        }
    }

    /**
     * Remove replies
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionRemoveReplies()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        return $this->asJson([
            'success' => true
        ]);
    }
}