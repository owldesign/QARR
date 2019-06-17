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

use craft\helpers\ArrayHelper;
use owldesign\qarr\models\Reply;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RepliesController extends Controller
{
    protected $allowAnonymous = ['actionSave', 'actionGetMarkup'];

    /**
     * Generate reply markup
     *
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetMarkup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $variables = [];
        $variables['id'] = Craft::$app->getRequest()->getBodyParam('id');
        $variables['placeholder'] = Craft::$app->getRequest()->getBodyParam('reply');
        $variables['elementId'] = Craft::$app->getRequest()->getBodyParam('elementId');
        $variables['authorId'] = Craft::$app->getRequest()->getBodyParam('authorId');
        $variables['author'] = Craft::$app->getRequest()->getBodyParam('author');
        $variables['dateCreated'] = Craft::$app->getRequest()->getBodyParam('dateCreated');
        $variables['dateUpdated'] = Craft::$app->getRequest()->getBodyParam('dateUpdated');

        $template = Craft::$app->view->renderTemplate('qarr/reviews/_includes/_modal', $variables);

        return $this->asJson([
            'success' => true,
            'template'   => $template
        ]);
    }

    /**
     * Save
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id         = Craft::$app->getRequest()->getBodyParam('id');
        $text       = Craft::$app->getRequest()->getBodyParam('reply');
        $elementId  = Craft::$app->getRequest()->getBodyParam('elementId');
        $authorId   = Craft::$app->getRequest()->getBodyParam('authorId');

        if ($id) {
            $record = QARR::$plugin->replies->getReplyById($id);
            $author = Craft::$app->users->getUserById($record->authorId);

            $model = new Reply($record->toArray(['id', 'reply', 'elementId', 'authorId', 'dateCreated', 'dateUpdated']));

            if (!$model) {
                throw new NotFoundHttpException('Reply not found');
            }
        } else {
            $model  = new Reply();
            $author = Craft::$app->getUser()->identity;
        }

        $model->reply       = $text;
        $model->elementId   = $elementId;
        $model->authorId    = $authorId;

        $response = QARR::$plugin->replies->save($model, $author);

        if ($response) {
            $array = ArrayHelper::toArray($response);

            $array['author'] = $author->friendlyName;

            return $this->asJson([
                'success' => true,
                'response'   => $array
            ]);
        } else {
            return $this->asJson([
                'success' => false,
                'errors' => $model->getErrors(),
            ]);

        }
    }

    /**
     * Remove reply
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


    /**
     * Delete Reply
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $this->requirePermission('qarr:editReviews');

        $replyId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        QARR::$plugin->replies->deleteReplyById($replyId);

        return $this->asJson(['success' => true]);
    }
}