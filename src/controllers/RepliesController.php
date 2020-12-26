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
    protected $allowAnonymous = [
        'save' => self::ALLOW_ANONYMOUS_LIVE,
        'get-markup' => self::ALLOW_ANONYMOUS_LIVE,
    ];

    /**
     * Generate reply markup
     *
     */
    public function actionGetMarkup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $replyId    = Craft::$app->getRequest()->getBodyParam('id');
        $reply      = QARR::$plugin->replies->getReplyModelById($replyId);

        $variables = [
            'response' => $reply
        ];

        $template = Craft::$app->view->renderTemplate('qarr/_elements/element-review-reply', $variables);


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

        $user       = Craft::$app->getUser()->getIdentity();

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
        $model->authorId    = $user->id;
        
        $response = QARR::$plugin->replies->save($model, $author);

        if ($response) {
            $model->id = $response->id;
            $model->uid = $response->uid;
            $model->dateCreated = $response->dateCreated;
            $model->dateUpdated = $response->dateUpdated;

            $template = Craft::$app->view->renderTemplate('qarr/_elements/element-review-reply', [
                'response' => $model
            ]);

            return $this->asJson([
                'success' => true,
                'author'   => $model->author,
                'response' => $model,
                'template' => $template
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